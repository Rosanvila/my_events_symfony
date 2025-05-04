<?php

namespace App\MessageHandler\Email;

use App\Message\Email\PaymentValidationEmailMessage;
use App\Repository\PaymentRepository;
use App\Service\Mailer\PaymentValidationEmail;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class PaymentValidationEmailHandler
{
    public function __construct(
        private readonly PaymentRepository $paymentRepository,
        private readonly PaymentValidationEmail $paymentValidationEmail
    ) {}

    public function __invoke(PaymentValidationEmailMessage $message): void
    {
        $payment = $this->paymentRepository->find($message->getPaymentId());

        if (!$payment) {
            throw new \RuntimeException(sprintf('Payment %d not found', $message->getPaymentId()));
        }

        $email = $this->paymentValidationEmail->createPaymentValidationEmail($payment);
        $this->paymentValidationEmail->getMailer()->send($email);
    }
}
