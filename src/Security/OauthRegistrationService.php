<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\GoogleUser;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final readonly class OauthRegistrationService
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    /**
     * @param GoogleUser $resourceOwner
     */
    public function persist(ResourceOwnerInterface $resourceOwner): User
    {
        $user = new User();
        $user->setEmail($resourceOwner->getEmail());
        $user->setGoogleId($resourceOwner->getId());
        $user->setFirstname($resourceOwner->getFirstName());
        $user->setLastname($resourceOwner->getLastName());
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, uniqid()));

        $this->userRepository->add($user, true);

        return $user;
    }
}
