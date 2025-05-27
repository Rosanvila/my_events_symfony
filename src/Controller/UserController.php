<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\ChangePasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/user')]
#[IsGranted('ROLE_USER')]
final class UserController extends AbstractController
{
    #[Route('/', name: 'app_user', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $events = $user->getEvents();

        // Récupérer les événements où l'utilisateur est participant
        $participatedEvents = [];
        foreach ($user->getParticipations() as $participation) {
            $participatedEvents[] = $participation->getEvent();
        }

        // Récupérer les événements où l'utilisateur est organisateur
        $organizedEvents = [];
        foreach ($events as $event) {
            if ($event->getOrganizer()->getId() === $user->getId()) {
                $organizedEvents[] = $event;
            }
        }

        // Déterminer si l'utilisateur est connecté via OAuth2
        $isOAuthUser = $user->isOAuth();

        // Formulaire de modification du profil (uniquement pour les utilisateurs non-OAuth)
        $form = null;
        if (!$isOAuthUser) {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $firstname = $form->get('firstname')->get('firstnameField')->getData();
                $lastname = $form->get('lastname')->get('lastnameField')->getData();

                $user->setFirstname($firstname);
                $user->setLastname($lastname);

                $entityManager->flush();
                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');
                return $this->redirectToRoute('app_user');
            }
        }

        // Formulaire de changement de mot de passe (uniquement pour les utilisateurs non-OAuth)
        $changePasswordForm = null;
        if (!$isOAuthUser) {
            $changePasswordForm = $this->createForm(ChangePasswordFormType::class);
            $changePasswordForm->handleRequest($request);

            if ($changePasswordForm->isSubmitted() && $changePasswordForm->isValid()) {
                $email = $changePasswordForm->get('email')->getData();
                $currentPassword = $changePasswordForm->get('currentPassword')->getData();
                $plainPassword = $changePasswordForm->get('plainPassword')->get('password')->getData();

                // Vérifier si l'email correspond à celui de l'utilisateur en session
                if ($email !== $user->getEmail()) {
                    $this->addFlash('error', 'L\'email ne correspond pas à votre compte.');
                    return $this->redirectToRoute('app_user');
                }

                // Vérifier si le mot de passe actuel est correct
                if (!$passwordHasher->isPasswordValid($user, $currentPassword)) {
                    $this->addFlash('error', 'Le mot de passe actuel est incorrect.');
                    return $this->redirectToRoute('app_user');
                }
                // Si tout est bon, procédez au changement de mot de passe
                $hashedPassword = $passwordHasher->hashPassword($user, $plainPassword);
                $user->setPassword($hashedPassword);

                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
                return $this->redirectToRoute('app_user');
            }

            // Récupérer le fournisseur OAuth actuel depuis la session via AbstractOAuthAuthenticator
            $currentOauthProvider = $request->getSession()->get('oauth_provider');

            // Si pas de fournisseur en session mais utilisateur OAuth, prendre le premier disponible
            if ($isOAuthUser && !$currentOauthProvider && !$user->getOauthConnections()->isEmpty()) {
                $currentOauthProvider = $user->getOauthConnections()->first()->getProvider();
            }

            // Récupérer tous les fournisseurs OAuth liés à l'utilisateur
            $connectedProviders = [];
            if ($isOAuthUser) {
                foreach ($user->getOauthConnections() as $connection) {
                    $connectedProviders[] = $connection->getProvider();
                }
            }
        }

        $currentOauthProvider = null;
        $connectedProviders = [];

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'form' => $form,
            'changePasswordForm' => $changePasswordForm,
            'isOAuthUser' => $isOAuthUser,
            'currentOauthProvider' => $currentOauthProvider,
            'connectedProviders' => $connectedProviders,
            'participatedEvents' => $participatedEvents,
            'organizedEvents' => $organizedEvents
        ]);
    }
}
