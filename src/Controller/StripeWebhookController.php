<?php

namespace App\Controller;

use App\Service\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/stripe/webhook')]
class StripeWebhookController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    #[Route('', name: 'app_stripe_webhook', methods: ['POST'])]
    public function handleWebhook(Request $request): Response
    {
        try {
            $this->stripeService->handleWebhook($request);
            return new Response('Webhook traité avec succès', Response::HTTP_OK);
        } catch (\Exception $e) {
            return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }
}
