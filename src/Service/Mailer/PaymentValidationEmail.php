<?php

namespace App\Service\Mailer;

use App\Entity\Payment;
use App\Service\PdfGenerator;
use Symfony\Component\Mime\Address;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class PaymentValidationEmail
{
    private Address|string|null $senderAddress;

    public function __construct(
        #[Autowire(env: 'PAYMENT_VALIDATION_SUBJECT')] private readonly string $subject,
        #[Autowire(env: 'AUTH_CODE_SENDER_EMAIL')] string|null $senderEmail,
        #[Autowire(env: 'AUTH_CODE_SENDER_NAME')] ?string $senderName = null,
        private readonly PdfGenerator $pdfGenerator,
        private readonly MailerInterface $mailer,
    ) {
        if (null !== $senderEmail && null !== $senderName) {
            $this->senderAddress = new Address($senderEmail, $senderName);
        } elseif (null !== $senderEmail) {
            $this->senderAddress = $senderEmail;
        }
    }

    public function createPaymentValidationEmail(Payment $payment): TemplatedEmail
    {
        $currentUser = $payment->getUser();
        $currentPaymentEmail = $currentUser->getEmail();
        $currentEvent = $payment->getEvent();

        // Générer le PDF
        $pdfContent = $this->pdfGenerator->generateTicket($payment);



        $email = new TemplatedEmail();
        $email->to($currentPaymentEmail);
        $email->subject($this->subject);
        $email->htmlTemplate('emails/payment_validation.html.twig');
        $email->context([
            'user' => $currentUser,
            'event' => $currentEvent,
        ]);

        // Attacher le PDF
        $email->attach($pdfContent, 'billet.pdf', 'application/pdf');

        if (null !== $this->senderAddress) {
            $email->from($this->senderAddress);
        }

        return $email;
    }
}
