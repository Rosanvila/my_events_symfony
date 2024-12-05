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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsLiveComponent('SubscriptionForm')]
class SubscriptionForm extends AbstractController
{
    use DefaultActionTrait;
    use ComponentWithFormTrait;

    private ?User $user = null;

    #[LiveProp]
    public ?User $initialFormData = null;

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(SubscriptionType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $form = $this->getForm();
        $plainPassword = $form->get('plainPassword')->get('password')->getData();

        if (!empty($plainPassword)) {
            $hashedPassword = $passwordHasher->hashPassword($this->user, $plainPassword);
            $this->user->setPassword($hashedPassword);
        }

        $this->submitForm();

        if (!$form->isValid()) {
            return;
        }

        $this->user = $form->getData();
        $this->user->setPassword($passwordHasher->hashPassword($this->user, $plainPassword));
        $this->user->setCreatedAt();
        $this->user->setRoles(['ROLE_USER']);

        $entityManager->persist($this->user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été créé avec succès !');

        return $this->redirectToRoute('app_home');
    }
}
