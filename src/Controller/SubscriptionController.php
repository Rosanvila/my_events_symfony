<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SubscriptionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\UserRepository;
use App\Security\EmailVerifier;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;


class SubscriptionController extends AbstractController
{

    public function __construct(private EmailVerifier $emailVerifier) {}

    public function register(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(SubscriptionType::class, $user);
        $form->handleRequest($request);


        return $this->render('login/form.html.twig', [
            'subForm' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route(path: '/verify/email', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, UserRepository $userRepository): Response
    {
        $id = $request->query->get('id');

        if (null === $id) {
            return $this->redirectToRoute('app_register');
        }

        $user = $userRepository->find($id);

        if (null === $user) {
            return $this->redirectToRoute('app_register');
        }

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $user);
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', 'Le lien de confirmation est invalide ou a expiré.');

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Votre adresse email a été vérifiée.');

        return $this->redirectToRoute('app_home');
    }
}
