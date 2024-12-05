<?php

namespace App\Twig\Components;

use App\Entity\User;
use App\Form\SubscriptionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsLiveComponent('SubscriptionForm')]
class SubscriptionForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    #[LiveProp]
    public ?User $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(SubscriptionType::class, $this->initialFormData);
    }

    public function save(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->submitForm();

        $user = $this->getForm()->getData();

        $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());

        $user->setCreatedAt(new \DateTime());
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($hashedPassword);
        


        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été créé avec succès !');

        return $this->redirectToRoute('app_home', [
            'id' => $user->getId(),
        ]);
    }
}
