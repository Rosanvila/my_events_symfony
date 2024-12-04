<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SubscriptionController extends AbstractController
{
    #[Route('/user/new', name: 'app_user_new')]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(SubscriptionType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_home');
        }

        return $this->render('login/form.html.twig', [
            'subForm' => $form->createView(),
            'user' => $user,
        ]);
    }
}