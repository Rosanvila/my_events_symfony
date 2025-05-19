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

        // Déterminer si l'utilisateur est connecté via OAuth2
        $isOAuthUser = $user->isOAuth();

        // Formulaire de modification du profil (uniquement pour les utilisateurs non-OAuth)
        $form = null;
        if (!$isOAuthUser) {
            $form = $this->createForm(UserType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // Récupérer les données des champs non mappés
                $firstname = $form->get('firstname')->get('firstnameField')->getData();
                $lastname = $form->get('lastname')->get('lastnameField')->getData();

                // Mettre à jour l'utilisateur
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

        // Récupérer le fournisseur OAuth actuel depuis la session
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
            'connectedProviders' => $connectedProviders
        ]);
    }
}
