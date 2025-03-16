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
final class PaidEvent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp(fieldName: 'formData')]
    public ?Event $event = null;

    #[LiveProp]
    public ?Event $initialFormData = null;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {}

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(EventType::class, $this->initialFormData);
    }

    #[LiveAction]
    public function save()
    {
        $this->submitForm();
        $form = $this->getForm();

        $event = $form->getData();
        $event->setCreatedAt(new \DateTime());

        if ($form->isValid()) {
            $event = $form->getData();


            $this->entityManager->persist($event);
            $this->entityManager->flush();
        }
        return $this->redirectToRoute('app_event_index');
    }
}
