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

        // Formulaire de modification du profil
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

        // Formulaire de changement de mot de passe
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

        return $this->render('user/index.html.twig', [
            'user' => $user,
            'form' => $form,
            'passwordForm' => $passwordForm,
        ]);
    }
}
