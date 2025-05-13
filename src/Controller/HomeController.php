<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    #[Route('/discover', name: 'app_discover')]
    public function discover(CategoryRepository $categoryRepository, EventRepository $eventRepository): Response
    {
        return $this->render('home/discover.html.twig', [
            'categories' => $categoryRepository->findAll(),
            'upcomingEvents' => $eventRepository->findUpcomingEvents(6)
        ]);
    }

    #[Route('/details', name: 'app_details')]
    public function details(): Response
    {
        return $this->render('home/details.html.twig');
    }
}
