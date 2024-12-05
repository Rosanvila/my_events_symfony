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
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(SubscriptionType::class, $user);
        $form->handleRequest($request);


        return $this->render('login/form.html.twig', [
            'subForm' => $form->createView(),
            'user' => $user,
        ]);
    }
}