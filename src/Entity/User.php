<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\Event;
use App\Entity\Participation;
use danielburger1337\SchebTwoFactorBundle\Model\TwoFactorEmailInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface, TwoFactorEmailInterface
{
    public function isEmailAuthEnabled(): bool
    {
        return true;
    }

    public function getEmailAuthRecipient(): string
    {
        return $this->getEmail();
    }

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Veuillez entrer une adresse email')]
    #[Assert\Email(message: 'Veuillez entrer une adresse email valide')]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre prénom')]
    private ?string $firstname = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Veuillez entrer votre nom')]
    private ?string $lastname = null;

    #[ORM\Column]
    private ?bool $isOAuth = false;

    #[ORM\Column(type: "string", nullable: true)]
    private ?string $authCode = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private $email_auth_code_expires_at = null;


    #[ORM\Column(type: 'boolean')]
    private $isVerified = false;

    #[ORM\OneToMany(mappedBy: 'organizer', targetEntity: Event::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $events;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Participation::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $participations;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: OauthConnection::class, orphanRemoval: true)]
    private Collection $oauthConnections;

    public function __construct()
    {
        $this->events = new ArrayCollection();
        $this->participations = new ArrayCollection();
        $this->oauthConnections = new ArrayCollection();
        $this->roles = ['ROLE_USER']; // default role
    }

    /**
     * @return Collection<int, Event>
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    public function addEvent(Event $event): static
    {
        if (!$this->events->contains($event)) {
            $this->events->add($event);
            $event->setOrganizer($this);
        }

        return $this;
    }

    public function removeEvent(Event $event): static
    {
        if ($this->events->removeElement($event)) {
            // set the owning side to null (unless already changed)
            if ($event->getOrganizer() === $this) {
                $event->setOrganizer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Participation>
     */
    public function getParticipations(): Collection
    {
        return $this->participations;
    }

    public function addParticipation(Participation $participation): static
    {
        if (!$this->participations->contains($participation)) {
            $this->participations->add($participation);
            $participation->setUser($this);
        }

        return $this;
    }

    public function removeParticipation(Participation $participation): static
    {
        if ($this->participations->removeElement($participation)) {
            // set the owning side to null (unless already changed)
            if ($participation->getUser() === $this) {
                $participation->setUser(null);
            }
        }

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function isVerified(): bool
    {
        return $this->isVerified;
    }

    public function setVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;
        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;
        return $this;
    }

    public function getEmailAuthCode(): string
    {
        if (null === $this->authCode) {
            throw new \LogicException('The email authentication code was not set');
        }

        return $this->authCode;
    }

    public function setEmailAuthCode(string $authCode): void
    {
        $this->authCode = $authCode;
    }

    public function getEmailAuthCodeExpiresAt(): \DateTimeImmutable|null
    {
        return new \DateTimeImmutable($this->email_auth_code_expires_at->format('Y-m-d H:i:s'));
    }

    public function setEmailAuthCodeExpiresAt(\DateTimeImmutable $expiresAt): void
    {
        $this->email_auth_code_expires_at = $expiresAt;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function isOAuth(): ?bool
    {
        return $this->isOAuth;
    }

    public function setIsOAuth(bool $isOAuth): static
    {
        $this->isOAuth = $isOAuth;
        return $this;
    }

    /**
     * @return Collection<int, OauthConnection>
     */
    public function getOauthConnections(): Collection
    {
        return $this->oauthConnections;
    }

    public function addOauthConnection(OauthConnection $oauthConnection): static
    {
        if (!$this->oauthConnections->contains($oauthConnection)) {
            $this->oauthConnections->add($oauthConnection);
            $oauthConnection->setUser($this);
        }

        return $this;
    }

    public function removeOauthConnection(OauthConnection $oauthConnection): static
    {
        if ($this->oauthConnections->removeElement($oauthConnection)) {
            // set the owning side to null (unless already changed)
            if ($oauthConnection->getUser() === $this) {
                $oauthConnection->setUser(null);
            }
        }

        return $this;
    }
}
