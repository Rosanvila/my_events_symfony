<?php
namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\HttpFoundation\Response;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

class FacebookAuthenticator extends AbstractOAuthAuthenticator
{
    protected string $serviceName = 'facebook';

    protected function getUserFromResourceOwner(ResourceOwnerInterface $resourceOwner, UserRepository $repository): ?User
    {
        if (!($resourceOwner instanceof FacebookUser)) {
            throw new \RuntimeException("expecting facebook user");
        }


        return $repository->findOneBy([
            'facebook_id' => $resourceOwner->getId(),
            'email' => $resourceOwner->getEmail()
        ]);
    }
}