<?php
declare(strict_types=1);

namespace App\Controller\Oauth2;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

final class SecurityController extends AbstractController
{
    public const SCOPES = [
        'google' => [],
        'facebook' => [],
    ];

    #[Route('/oauth/connect/{service}', name: 'auth_oauth_connect', methods: ['GET'])]
    public function connect(string $service, ClientRegistry $clientRegistry): RedirectResponse
    {
        if (!in_array($service, array_keys(self::SCOPES))) {
            throw $this->createNotFoundException();
        }

        $redirectParams = [];
        if ($service === 'facebook') {
            $redirectParams = ['service' => $service];
        }

        return $clientRegistry
            ->getClient($service)
            ->redirect(self::SCOPES[$service], $redirectParams);
    }

    #[Route('/oauth/check/{service}', name: 'auth_oauth_check', methods: ['GET', 'POST'])]
    public function check(string $service): Response
    {
        return new Response(status: 200);
    }
}