<?php

declare(strict_types=1);

namespace App\Controller\Oauth2;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SecurityController extends AbstractController
{
    public function login()
    {

        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('security/login.html.twig');
    }
}