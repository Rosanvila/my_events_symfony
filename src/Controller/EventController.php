<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Security\Voter\EventVoter;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository, CategoryRepository $categoryRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_event_new', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function new(): Response
    {
        // Le formulaire est maintenant géré par le Twig Component
        return $this->render('event/new.html.twig');
    }

    #[Route('/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event): Response
    {
        // Vérifier les autorisations de lecture
        $this->denyAccessUnlessGranted(EventVoter::VIEW, $event);

        $isParticipant = false;
        $isOrganizer = false;

        if ($this->getUser()) {
            $isParticipant = $event->getParticipants()->exists(function ($key, $participation) {
                return $participation->getUser() === $this->getUser();
            });
            $isOrganizer = $event->getOrganizer() === $this->getUser();
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
            'is_participant' => $isParticipant,
            'is_organizer' => $isOrganizer
        ]);
    }

    #[Route('/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        // Utiliser le Voter pour vérifier les autorisations
        $this->denyAccessUnlessGranted(EventVoter::EDIT, $event);

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Votre événement a été modifié avec succès !');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('event/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_event_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        // Utiliser le Voter pour vérifier les autorisations
        $this->denyAccessUnlessGranted(EventVoter::DELETE, $event);

        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();

            $this->addFlash('success', 'Votre événement a été supprimé avec succès !');
        }

        return $this->redirectToRoute('app_event_index', [], Response::HTTP_SEE_OTHER);
    }
}
