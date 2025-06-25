<?php

namespace App\Twig\Components;

use App\Entity\Category;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\String\UnicodeString;

#[AsLiveComponent('EventSearch')]
final class EventSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public ?string $name = null;

    #[LiveProp(writable: true, url: true)]
    public ?string $location = null;

    #[LiveProp(writable: true, url: true, format: 'Y-m-d')]
    public ?string $startDate = null;

    #[LiveProp(writable: true, url: true)]
    public ?Category $category = null;

    public function __construct(
        private EventRepository $eventRepository,
        private CategoryRepository $categoryRepository,
        private UrlGeneratorInterface $urlGenerator
    ) {}

    #[LiveAction]
    public function resetEventFilters(): void
    {
        $this->name = null;
        $this->location = null;
        $this->startDate = null;
    }

    #[LiveAction]
    public function resetCategory(): void
    {
        $this->category = null;
    }

    #[LiveAction]
    public function search(): void
    {
        // La méthode est vide car elle est utilisée uniquement pour déclencher la mise à jour du composant
    }

    public function getEvents(): array
    {
        $startDate = null;
        if ($this->startDate && trim($this->startDate) !== '') {
            try {
                $startDate = new \DateTimeImmutable($this->startDate, new \DateTimeZone('UTC'));

                // Validation de la date (pas plus de 2 ans dans le futur)
                $maxDate = new \DateTimeImmutable('+2 years');
                if ($startDate > $maxDate) {
                    $startDate = null;
                }
            } catch (\Exception $e) {
                // Si la date n'est pas valide, on l'ignore
                $startDate = null;
            }
        }

        // Limiter le nombre de résultats pour éviter les attaques par déni de service
        $searchParams = [
            'name' => $this->name ? trim($this->name) : null,
            'location' => $this->location ? trim($this->location) : null,
            'startDate' => $startDate,
            'category' => $this->category,
            'limit' => 50, // Limiter à 50 résultats max
        ];

        return $this->eventRepository->search($searchParams);
    }

    public function getCategories(): array
    {
        return $this->categoryRepository->findAll();
    }
}
