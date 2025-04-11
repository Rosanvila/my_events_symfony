<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Participation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Stripe\Exception\ApiErrorException;
use Stripe\Exception\SignatureVerificationException;

class StripeService
{
    private const PAYMENT_STATUS_COMPLETED = 'completed';
    private const PARTICIPATION_STATUS_CONFIRMED = 'confirmed';
    private const CURRENCY_EUR = 'eur';

    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire('%env(STRIPE_SECRET_KEY)%')] private string $stripeSecret,
        #[Autowire('%env(STRIPE_WEBHOOK_SECRET)%')] private string $webhookSecret,
        private UrlGeneratorInterface $urlGenerator,
    ) {
        Stripe::setApiKey($this->stripeSecret);
    }

    public function getStripeSecret(): string
    {
        return $this->stripeSecret;
    }

    public function createCheckoutSession(Event $event, User $user): Session
    {
        try {
            $existingParticipation = $this->entityManager->getRepository(Participation::class)
                ->findOneBy(['user' => $user, 'event' => $event]);

            if ($existingParticipation) {
                throw new \Exception('Vous êtes déjà inscrit à cet événement.');
            }

            if ($event->getParticipants()->count() >= $event->getMaxParticipants()) {
                throw new \Exception('Cet événement est complet.');
            }

            // Création de la session
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => self::CURRENCY_EUR,
                        'product_data' => [
                            'name' => $event->getName(),
                            'description' => $event->getDescription(),
                        ],
                        'unit_amount' => $event->getPrice() * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'metadata' => [
                    'event_id' => $event->getId(),
                    'user_id' => $user->getId()
                ],
                'success_url' => $this->urlGenerator->generate('app_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => $this->urlGenerator->generate('app_payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return $session;
        } catch (ApiErrorException $e) {
            throw new \Exception('Erreur lors de la création de la session de paiement : ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Une erreur inattendue est survenue : ' . $e->getMessage());
        }
    }


    public function getPaymentBySessionId(string $sessionId): ?Payment
    {
        return $this->entityManager->getRepository(Payment::class)
            ->findOneBy(['stripe_session_id' => $sessionId]);
    }


    private function handleSuccessfulPayment(Session $session): void
    {
        try {
            if (!isset($session->metadata['event_id']) || !isset($session->metadata['user_id'])) {
                throw new \Exception('Métadonnées manquantes dans la session');
            }

            $event = $this->entityManager->getRepository(Event::class)
                ->find($session->metadata['event_id']);
            $user = $this->entityManager->getRepository(User::class)
                ->find($session->metadata['user_id']);

            if (!$event || !$user) {
                throw new \Exception('Événement ou utilisateur non trouvé');
            }

            $existingPayment = $this->getPaymentBySessionId($session->id);

            if ($existingPayment) {
                return;
            }

            $participation = new Participation();
            $participation->setUser($user);
            $participation->setEvent($event);
            $participation->setStatus(self::PARTICIPATION_STATUS_CONFIRMED);

            $payment = new Payment();
            $payment->setUser($user);
            $payment->setEvent($event);
            $payment->setAmount($event->getPrice());
            $payment->setCurrency(self::CURRENCY_EUR);
            $payment->setStatus(self::PAYMENT_STATUS_COMPLETED);
            $payment->setStripeSessionId($session->id);
            $payment->setStripePaymentIntentId($session->payment_intent);

            $this->entityManager->persist($participation);
            $this->entityManager->persist($payment);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors du traitement du paiement : ' . $e->getMessage());
        }
    }

    public function handleWebhook(Request $request): void
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->webhookSecret
            );

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->handleSuccessfulPayment($session);
                    break;

                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->handleSuccessfulPayment($paymentIntent);
                    break;

                default:
                    throw new \Exception('Type d\'événement non géré : ' . $event->type);
            }
        } catch (SignatureVerificationException $e) {
            throw new \Exception('Signature de webhook invalide : ' . $e->getMessage());
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors du traitement du webhook : ' . $e->getMessage());
        }
    }
}
