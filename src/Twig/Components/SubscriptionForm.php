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
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\VarDumper\VarDumper;

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
    public function save(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, Security $security)
    {
        $form = $this->getForm();
        $this->submitForm();

        if (!$form->isValid()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs.');
            return;
        }

        $this->user = $form->getData();

        $plainPassword = $form->get('plainPassword')->get('password')->getData();

        if (!empty($plainPassword)) {
            $hashedPassword = $passwordHasher->hashPassword($this->user, $plainPassword);
            $this->user->setPassword($hashedPassword);
        } else {
            $this->addFlash('error', 'Le mot de passe ne peut pas être vide.');
            return;
        }

        $this->user->setRoles(['ROLE_USER']);

        try {
            $entityManager->persist($this->user);
            $entityManager->flush();
            $this->addFlash('success', 'Votre compte a été créé avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de l\'enregistrement de l\'utilisateur.');
            return;
        }

        $security->login($this->user, 'form_login', 'main');
        return $this->redirectToRoute('app_home');
    }
}
