<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

class TwoFactorConditionService
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function shouldPerformTwoFactorAuthentication(): bool
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            return false;
        }

        return !$user->isOAuth();
    }
}
