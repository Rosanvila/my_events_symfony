<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use App\Entity\User;
use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/participation')]
class ParticipationController extends AbstractController
{

    #[Route('/reserve/{id}', name: 'app_participation_reserve', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function reserve(Event $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $existingParticipation = $entityManager->getRepository(Participation::class)
            ->findOneBy(['user' => $user, 'event' => $event]);

        if ($existingParticipation) {
            $this->addFlash('error', 'Vous êtes déjà inscrit à cet événement.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        if ($event->getParticipants()->count() >= $event->getMaxParticipants()) {
            $this->addFlash('error', 'Cet événement est complet.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $participation = new Participation();
        $participation->setUser($user);
        $participation->setEvent($event);
        $participation->setStatus('confirmed');

        $entityManager->persist($participation);
        $entityManager->flush();

        $this->addFlash('success', 'Vous êtes inscrit à l\'événement !');

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }


    #[Route('/cancel/{id}', name: 'app_participation_cancel', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function cancel(Event $event, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        $participation = $entityManager->getRepository(Participation::class)
            ->findOneBy(['user' => $user, 'event' => $event]);

        if (!$participation) {
            $this->addFlash('warning', 'Vous n\'êtes pas inscrit à cet événement.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $entityManager->remove($participation);
        $entityManager->flush();

        $this->addFlash('success', 'Votre inscription a été annulée.');

        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}
