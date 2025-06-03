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
        private readonly MailerInterface $mailer,
        private readonly PdfGenerator $pdfGenerator,
        #[Autowire(env: 'PAYMENT_VALIDATION_SUBJECT')] private readonly string $subject,
        #[Autowire(env: 'AUTH_CODE_SENDER_EMAIL')] private readonly ?string $senderEmail = null,
        #[Autowire(env: 'AUTH_CODE_SENDER_NAME')] private readonly ?string $senderName = null
    ) {
        if (null !== $this->senderEmail && null !== $this->senderName) {
            $this->senderAddress = new Address($this->senderEmail, $this->senderName);
        } elseif (null !== $this->senderEmail) {
            $this->senderAddress = $this->senderEmail;
        }
    }

    public function createPaymentValidationEmail(Payment $payment): TemplatedEmail
    {
        $currentUser = $payment->getUser();
        $currentPaymentEmail = $currentUser->getEmail();
        $currentEvent = $payment->getEvent();

        $pdfContent = $this->pdfGenerator->generateTicket($payment);

        $email = new TemplatedEmail();
        $email->to($currentPaymentEmail);
        $email->subject($this->subject);
        $email->htmlTemplate('emails/payment_validation.html.twig');
        $email->context([
            'user' => $currentUser,
            'event' => $currentEvent,
        ]);

        // PDF dans l'email
        $email->attach($pdfContent, 'billet.pdf', 'application/pdf');

        if (null !== $this->senderAddress) {
            $email->from($this->senderAddress);
        }

        return $email;
    }

    public function getMailer(): MailerInterface
    {
        return $this->mailer;
    }
}
