<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SubscriptionType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SubscriptionController extends AbstractController
{
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
}