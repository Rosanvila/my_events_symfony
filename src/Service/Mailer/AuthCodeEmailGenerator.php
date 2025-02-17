<?php declare(strict_types=1);

namespace App\Service\Mailer;

use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use danielburger1337\SchebTwoFactorBundle\Mailer\AuthCodeEmailGeneratorInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class AuthCodeEmailGenerator implements AuthCodeEmailGeneratorInterface
{
    private Address|string|null $senderAddress;

    public function __construct(
        #[Autowire(env: 'AUTH_CODE_SUBJECT')] private readonly string $subject,
        #[Autowire(env: 'AUTH_CODE_SENDER_EMAIL')] string|null $senderEmail,
        #[Autowire(env: 'AUTH_CODE_SENDER_NAME')] ?string $senderName = null,
    ) {
        if (null !== $senderEmail && null !== $senderName) {
            $this->senderAddress = new Address($senderEmail, $senderName);
        } elseif (null !== $senderEmail) {
            $this->senderAddress = $senderEmail;
        }
    }

    public function createAuthCodeEmail(TwoFactorEmailInterface $user): TemplatedEmail
    {
        $authCode = $user->getEmailAuthCode();
        if (null === $authCode) {
            throw new \InvalidArgumentException();
        }

        $message = new TemplatedEmail();
        $expiresAt = $user->getEmailAuthCodeExpiresAt()->setTimezone(new \DateTimeZone('Europe/Paris'))->format("H:i");
        // TODO : Intl et timezone du mail basé sur config user
        $message
            ->to($user->getEmailAuthRecipient())
            ->subject('Votre code', ['expiresAt' => $expiresAt])
            ->htmlTemplate('security/email/authcode.html.twig')
            ->context([
                'authCode' => $authCode,
                'expiresAt' => $expiresAt,
                'subject' => $this->subject,
            ])
        ;

        if (null !== $this->senderAddress) {
            $message->from($this->senderAddress);
        }

        return $message;
    }
}