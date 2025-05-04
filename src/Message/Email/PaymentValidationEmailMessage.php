<?php

namespace App\Message\Email;

class PaymentValidationEmailMessage
{
    public function __construct(
        private readonly int $paymentId,
        private readonly string $recipientEmail
    ) {}

    public function getPaymentId(): int
    {
        return $this->paymentId;
    }

    public function getRecipientEmail(): string
    {
        return $this->recipientEmail;
    }
}
