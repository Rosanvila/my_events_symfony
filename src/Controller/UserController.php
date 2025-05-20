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
        $passwordForm = null;
        if (!$isOAuthUser) {
            $passwordForm = $this->createForm(ChangePasswordFormType::class);
            $passwordForm->handleRequest($request);

            if ($passwordForm->isSubmitted() && $passwordForm->isValid()) {
                $user->setPassword(
                    $passwordHasher->hashPassword(
                        $user,
                        $passwordForm->get('plainPassword')->getData()
                    )
                );

                $entityManager->flush();
                $this->addFlash('success', 'Votre mot de passe a été modifié avec succès.');
                return $this->redirectToRoute('app_user');
            }
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

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'form' => $form,
            'passwordForm' => $passwordForm,
            'isOAuthUser' => $isOAuthUser,
            'currentOauthProvider' => $currentOauthProvider,
            'connectedProviders' => $connectedProviders,
            'participatedEvents' => $participatedEvents,
            'organizedEvents' => $organizedEvents
        ]);
    }
}
