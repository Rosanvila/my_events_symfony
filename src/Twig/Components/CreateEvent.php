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

    private Event $event;

    #[LiveProp]
    public ?Event $initialFormData = null;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $this->event = new Event();
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(EventType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();
        $this->event = $this->getForm()->getData();


        $this->event->setOrganizer($this->getUser());

        $this->entityManager->persist($this->event);
        $this->entityManager->flush();

        return $this->redirectToRoute('app_event_index');
    }
}
