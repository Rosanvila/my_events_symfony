<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Payment;
use App\Service\StripeService;
use App\Service\Mailer\PaymentValidationEmail;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private StripeService $stripeService,
        private PaymentValidationEmail $paymentValidationEmail
    ) {}

    #[Route('/checkout/{id}', name: 'app_checkout_stripe', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function checkout(Event $event): Response
    {
        try {
            $session = $this->stripeService->createCheckoutSession($event, $this->getUser());
            return $this->json(['url' => $session->url]);
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/success', name: 'app_payment_success')]
    #[IsGranted('ROLE_USER')]
    public function success(): Response
    {
        try {
            $session_id = $_GET['session_id'] ?? null;

            if (!$session_id) {
                throw new \Exception('Session ID manquant.');
            }

            // Récupérer la session Stripe
            $session = Session::retrieve($session_id);

            if ($session->payment_status !== 'paid') {
                throw new \Exception('Le paiement n\'a pas été confirmé.');
            }

            $payment = $this->stripeService->getPaymentBySessionId($session_id);
            if (!$payment) {
                throw new \Exception('Paiement non trouvé.');
            }

            // email de validation
            $this->paymentValidationEmail->createPaymentValidationEmail($payment);

            $this->addFlash('success', 'Votre paiement a été accepté, un email de confirmation vous a été envoyé.');
            return $this->redirectToRoute('app_event_index');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_event_index');
        }
    }

    #[Route('/cancel', name: 'app_payment_cancel')]
    #[IsGranted('ROLE_USER')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_event_index');
    }
}
