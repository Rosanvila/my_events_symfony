<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\User;
use App\Entity\Category;
use App\Entity\Participation;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Event
{
    private const DEFAULT_PHOTO = "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";
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 55)]
    private string $currentState = 'upcoming';  

    #[ORM\Column(nullable: true, type: Types::TEXT, length: 65535)]
    private ?string $description = null;

    #[ORM\Column(nullable: true, type: Types::TEXT)]
    private ?string $photo = self::DEFAULT_PHOTO;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\GreaterThan(0)]
    private ?int $maxParticipants = null;

    #[ORM\Column]
    private bool $isPaid = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    #[ORM\ManyToOne(targetEntity: Category::class, inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Category $category = null;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Participation::class, cascade: ['persist', 'remove'])]
    private Collection $participants;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\Positive]
    private ?string $price = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $startDate = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank]
    #[Assert\GreaterThan(propertyPath: 'startDate')]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $location = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): static
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participation $participant): static
    {
        if (!$this->participants->contains($participant)) {
            $this->participants->add($participant);
            $participant->setEvent($this);
        }

        return $this;
    }

    public function removeParticipant(Participation $participant): static
    {
        if ($this->participants->removeElement($participant)) {
            // set the owning side to null (unless already changed)
            if ($participant->getEvent() === $this) {
                $participant->setEvent(null);
            }
        }

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): static
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getPhoto(): ?string
    {
        if (!is_null($this->photo)) {
            return $this->photo;
        } else {
            return self::DEFAULT_PHOTO;
        }
    }

    public function getSrcPhoto(): ?string
    {
        return "data:image/jpeg;base64," . $this->getPhoto();
    }

    public function setPhoto(?string $photo): static
    {
        if (!is_null($photo)) {
            $this->photo = $photo;
        } else {
            $this->photo = self::DEFAULT_PHOTO;
        }
        return $this;
    }

    public function getMaxParticipants(): ?int
    {
        return $this->maxParticipants;
    }

    public function setMaxParticipants(int $maxParticipants): static
    {
        $this->maxParticipants = $maxParticipants;
        return $this;
    }

    public function isIsPaid(): bool
    {
        return $this->isPaid;
    }

    public function setIsPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;
        return $this;
    }

    public function getCurrentState(): string
    {
        return $this->currentState;
    }

    public function setCurrentState(string $currentState): static
    {
        $this->currentState = $currentState;
        return $this;
    }
}
