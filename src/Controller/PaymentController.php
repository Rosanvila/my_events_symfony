<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        #[Autowire('%env(STRIPE_SECRET_KEY)%')] private string $stripeSecret
    ) {}

    #[Route('/checkout/{id}', name: 'app_checkout_stripe', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function checkout(Event $event): Response
    {
        try {
            $existingParticipation = $this->entityManager->getRepository(Participation::class)
                ->findOneBy(['user' => $this->getUser(), 'event' => $event]);

            if ($existingParticipation) {
                return $this->json([
                    'error' => 'Vous êtes déjà inscrit à cet événement.'
                ], 400);
            }

            if ($event->getParticipants()->count() >= $event->getMaxParticipants()) {
                return $this->json([
                    'error' => 'Cet événement est complet.'
                ], 400);
            }

            Stripe::setApiKey($this->stripeSecret);

            $session = Session::create([
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => $event->getName(),
                            'description' => $event->getDescription(),
                        ],
                        'unit_amount' => $event->getPrice() * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $this->generateUrl('app_payment_success', [
                    'event_id' => $event->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('app_payment_cancel', [
                    'event_id' => $event->getId()
                ], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return $this->json([
                'url' => $session->url
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Une erreur est survenue lors de la création de la session de paiement : ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/success', name: 'app_payment_success')]
    #[IsGranted('ROLE_USER')]
    public function success(int $eventId): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        // Créer la participation
        $participation = new Participation();
        $participation->setUser($this->getUser());
        $participation->setEvent($event);
        $participation->setStatus('confirmed');

        $this->entityManager->persist($participation);
        $this->entityManager->flush();

        $this->addFlash('success', 'Vous êtes inscrit à l\'événement !');
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }

    #[Route('/cancel', name: 'app_payment_cancel')]
    #[IsGranted('ROLE_USER')]
    public function cancel(int $eventId): Response
    {
        $event = $this->entityManager->getRepository(Event::class)->find($eventId);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}
