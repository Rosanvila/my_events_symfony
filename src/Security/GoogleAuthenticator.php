<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\OauthConnectionRepository;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Routing\RouterInterface;

class GoogleAuthenticator extends AbstractOAuthAuthenticator
{
    protected string $serviceName = 'google';

    public function __construct(
        ClientRegistry $clientRegistry,
        RouterInterface $router,
        UserRepository $repository,
        OauthRegistrationService $registrationService,
        private OauthConnectionRepository $oauthConnectionRepository
    ) {
        parent::__construct($clientRegistry, $router, $repository, $registrationService);
    }

    protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UserRepository $repository): ?User
    {
        if (!($resourceOwner instanceof GoogleUser)) {
            throw new \RuntimeException("expecting google user");
        }

        if (true !== ($resourceOwner->toArray()['email_verified'] ?? null)) {
            throw new AuthenticationException("email not verified");
        }

        // Search by oauth connection
        $oauthConnection = $this->oauthConnectionRepository->findOneByProviderAndProviderId(
            'google',
            $resourceOwner->getId()
        );

        if ($oauthConnection) {
            return $oauthConnection->getUser();
        }

        // If no connection found, search by email
        $oauthConnection = $this->oauthConnectionRepository->findOneByProviderAndEmail(
            'google',
            $resourceOwner->getEmail()
        );

        return $oauthConnection ? $oauthConnection->getUser() : null;
    }
}
