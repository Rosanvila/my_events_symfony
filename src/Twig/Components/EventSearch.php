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

    public function sanitizeInput(?string $input): ?string
    {
        if ($input === null) {
            return null;
        }

        $string = new UnicodeString($input);

        $string = $string->trim();

        return htmlspecialchars($string->toString(), ENT_QUOTES, 'UTF-8');
    }

    #[LiveAction]
    public function search(): void {}

    public function getEvents(): array
    {
        $startDate = $this->startDate ? new \DateTimeImmutable($this->startDate, new \DateTimeZone('UTC')) : null;

        return $this->eventRepository->search([
            'name' => $this->sanitizeInput($this->name),
            'location' => $this->sanitizeInput($this->location),
            'startDate' => $startDate,
            'category' => $this->category,
        ]);
    }

    public function getCategories(): array
    {
        return $this->categoryRepository->findAll();
    }
}
