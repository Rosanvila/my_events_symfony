<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components;

use App\Form\EventType;
use App\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('event_form')]
final class CreateEvent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    private ?Event $event = null;

    #[LiveProp]
    public ?Event $initialFormData = null;


    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(EventType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function save(EntityManagerInterface $entityManager)
    {
        $form = $this->getForm();
        $this->submitForm();

        if (!$form->isValid()) {
            $this->addFlash('error', 'Le formulaire contient des erreurs.');
            return;
        }

        $this->event = $form->getData();

        if (empty($this->event->getOrganizer())) {
            $this->event->setOrganizer($this->getUser());
        }

        if (empty($this->event->getCreatedAt())) {
            $this->event->setCreatedAt(new \DateTimeImmutable());
        }

        // Gestion de l'upload de la photo
        $photoFile = $form->get('photo')->getData();
        if ($photoFile) {
            $newFilename = uniqid() . '.' . $photoFile->guessExtension();
            $photoFile->move(
                $this->getParameter('events_directory'),
                $newFilename
            );
            $this->event->setPhoto('/uploads/events/' . $newFilename);
        }

        $entityManager->persist($this->event);
        $entityManager->flush();

        return $this->redirectToRoute('app_event_index');
    }
}
