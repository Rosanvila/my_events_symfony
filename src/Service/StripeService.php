<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Payment;
use App\Entity\Participation;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
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
        private LoggerInterface $logger
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

            if ($event->getPrice() <= 0) {
                throw new \Exception('Le prix de l\'événement doit être supérieur à 0.');
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

            $this->logger->info('Checkout session created', [
                'session_id' => $session->id,
                'event_id' => $event->getId(),
                'user_id' => $user->getId()
            ]);

            return $session;
        } catch (ApiErrorException $e) {
            $this->logger->error('Stripe API error', [
                'error' => $e->getMessage(),
                'code' => $e->getStripeCode()
            ]);
            throw new \Exception('Erreur lors de la création de la session de paiement : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Unexpected error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Une erreur inattendue est survenue : ' . $e->getMessage());
        }
    }

    public function handleWebhook(Request $request): void
    {
        $payload = $request->getContent();
        $sigHeader = $request->headers->get('Stripe-Signature');

        $this->logger->info('Webhook received', [
            'signature' => $sigHeader,
            'payload' => $payload
        ]);

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                $this->webhookSecret
            );

            $this->logger->info('Webhook event constructed', [
                'type' => $event->type,
                'id' => $event->id,
                'object' => $event->data->object
            ]);

            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $this->logger->info('Processing checkout.session.completed', [
                        'session_id' => $session->id,
                        'payment_status' => $session->payment_status,
                        'metadata' => $session->metadata
                    ]);
                    $this->handleSuccessfulPayment($session);
                    break;

                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $this->logger->info('Payment intent succeeded', [
                        'payment_intent_id' => $paymentIntent->id,
                        'status' => $paymentIntent->status
                    ]);
                    break;

                default:
                    $this->logger->info('Unhandled event type', [
                        'type' => $event->type
                    ]);
                    break;
            }
        } catch (SignatureVerificationException $e) {
            $this->logger->error('Webhook signature verification failed', [
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Signature de webhook invalide : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error('Webhook error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Erreur lors du traitement du webhook : ' . $e->getMessage());
        }
    }

    private function handleSuccessfulPayment(Session $session): void
    {
        try {
            $this->logger->info('Handling successful payment', [
                'session_id' => $session->id,
                'metadata' => $session->metadata
            ]);

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

            // Vérifier si le paiement existe déjà
            $existingPayment = $this->entityManager->getRepository(Payment::class)
                ->findOneBy(['stripe_session_id' => $session->id]);

            if ($existingPayment) {
                $this->logger->info('Payment already exists', [
                    'payment_id' => $existingPayment->getId()
                ]);
                return;
            }

            // Création de la participation
            $participation = new Participation();
            $participation->setUser($user);
            $participation->setEvent($event);
            $participation->setStatus(self::PARTICIPATION_STATUS_CONFIRMED);

            // Enregistrement du paiement
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

            $this->logger->info('Payment and participation created', [
                'participation_id' => $participation->getId(),
                'payment_id' => $payment->getId()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Error handling successful payment', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('Erreur lors du traitement du paiement : ' . $e->getMessage());
        }
    }
}
