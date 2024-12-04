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

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager)
    {
        $this->submitForm();

        $user = $this->getForm()->getData();
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre compte a été créé avec succès !');

        return $this->redirectToRoute('app_home', [
            'id' => $user->getId(),
        ]);
    }
}
