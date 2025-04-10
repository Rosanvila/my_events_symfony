<?php

namespace App\Service;

use App\Entity\Payment;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;

class PdfGenerator extends AbstractController
{
    public function __construct(
        private Environment $twig,
        private DompdfWrapperInterface $wrapper,
    ) {}

    public function generateTicket(Payment $payment): string
    {
        $html = $this->renderView('pdf/ticket.html.twig', [
            'payment' => $payment,
            'user' => $payment->getUser(),
            'event' => $payment->getEvent(),
        ]);

        return $this->wrapper->getPdf($html);
    }
}
