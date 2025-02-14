<?php

namespace App\Service\Mailer;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeEmailGeneratorInterface;


class AuthCodeEmailGenerator implements AuthCodeEmailGeneratorInterface
{
    public function createAuthCodeEmail(TwoFactorEmailInterface $user): TemplatedEmail
    {
        $authCode = $user->getEmailAuthCode();
        if (null === $authCode) {
            throw new \InvalidArgumentException('User must have an authentication code.');
        }

        $email = new TemplatedEmail();
        $expiresAt = $user->getEmailAuthCodeExpiresAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format('H:i');

        $email
            ->to($user->getEmailAuthRecipient())
            ->subject('Code de vérification, valide jusqu\'à ' . $expiresAt)
            ->htmlTemplate('security/email/authcode.html.twig')
            ->context([
                'authCode' => $authCode,
                'expiresAt' => $expiresAt,
            ]);

        return $email;
    }
}
