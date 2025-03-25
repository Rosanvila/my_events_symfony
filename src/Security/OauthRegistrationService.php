<?php

namespace App\Security;

use App\Entity\User;
use App\Entity\OauthConnection;
use App\Repository\UserRepository;
use App\Repository\OauthConnectionRepository;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Provider\GoogleUser;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class OauthRegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private OauthConnectionRepository $oauthConnectionRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @param GoogleUser|FacebookUser $resourceOwner
     */
    public function persist(ResourceOwnerInterface $resourceOwner): User
    {
        $provider = $resourceOwner instanceof GoogleUser ? 'google' : 'facebook';

        // search if a connection exists
        $existingConnection = $this->oauthConnectionRepository->findOneByProviderAndProviderId(
            $provider,
            $resourceOwner->getId()
        );

        if ($existingConnection) {
            return $existingConnection->getUser();
        }

        // search if a user exists with this email
        $existingUser = $this->userRepository->findOneBy(['email' => $resourceOwner->getEmail()]);

        if ($existingUser) {
            $oauthConnection = new OauthConnection();
            $oauthConnection->setUser($existingUser);
            $oauthConnection->setProvider($provider);
            $oauthConnection->setProviderId($resourceOwner->getId());
            $oauthConnection->setEmail($resourceOwner->getEmail());

            $this->oauthConnectionRepository->save($oauthConnection, true);

            if (!$existingUser->isOAuth()) {
                $existingUser->setIsOAuth(true);
                $this->userRepository->add($existingUser, true);
            }

            return $existingUser;
        }

        // create a new user
        $user = new User();
        $user->setEmail($resourceOwner->getEmail());
        $user->setFirstname($resourceOwner->getFirstName());
        $user->setLastname($resourceOwner->getLastName());
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, uniqid()));
        $user->setIsOAuth(true);

        $this->userRepository->add($user, true);

        // create the oauth connection
        $oauthConnection = new OauthConnection();
        $oauthConnection->setUser($user);
        $oauthConnection->setProvider($provider);
        $oauthConnection->setProviderId($resourceOwner->getId());
        $oauthConnection->setEmail($resourceOwner->getEmail());

        $this->oauthConnectionRepository->save($oauthConnection, true);

        return $user;
    }
}
