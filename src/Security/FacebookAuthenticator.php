<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\OauthConnectionRepository;
use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\HttpFoundation\Response;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\Routing\RouterInterface;

class FacebookAuthenticator extends AbstractOAuthAuthenticator
{
    protected string $serviceName = 'facebook';

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
        if (!($resourceOwner instanceof FacebookUser)) {
            throw new \RuntimeException("expecting facebook user");
        }

        // Recherche par connexion OAuth
        $oauthConnection = $this->oauthConnectionRepository->findOneByProviderAndProviderId(
            'facebook',
            $resourceOwner->getId()
        );

        if ($oauthConnection) {
            return $oauthConnection->getUser();
        }

        // Si aucune connexion trouvée, recherche par email
        $oauthConnection = $this->oauthConnectionRepository->findOneByProviderAndEmail(
            'facebook',
            $resourceOwner->getEmail()
        );

        return $oauthConnection ? $oauthConnection->getUser() : null;
    }
}
