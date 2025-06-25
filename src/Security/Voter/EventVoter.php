<?php

namespace App\Security\Voter;

use App\Entity\Event;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    public const VIEW = 'EVENT_VIEW';
    public const EDIT = 'EVENT_EDIT';
    public const DELETE = 'EVENT_DELETE';
    public const PARTICIPATE = 'EVENT_PARTICIPATE';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // Si l'attribut n'est pas l'un de ceux que nous supportons, retourner false
        if (!in_array($attribute, [self::VIEW, self::EDIT, self::DELETE, self::PARTICIPATE])) {
            return false;
        }

        // Si le sujet n'est pas un Event, retourner false
        if (!$subject instanceof Event) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Si l'utilisateur n'est pas connecté, il ne peut que voir les événements publics
        if (!$user instanceof UserInterface) {
            return $attribute === self::VIEW;
        }

        /** @var Event $event */
        $event = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($event, $user),
            self::EDIT => $this->canEdit($event, $user),
            self::DELETE => $this->canDelete($event, $user),
            self::PARTICIPATE => $this->canParticipate($event, $user),
            default => false,
        };
    }

    private function canView(Event $event, UserInterface $user): bool
    {
        // Tout le monde peut voir les événements publics
        return true;
    }

    private function canEdit(Event $event, UserInterface $user): bool
    {
        // Seul l'organisateur de l'événement peut le modifier
        return $event->getOrganizer() === $user;
    }

    private function canDelete(Event $event, UserInterface $user): bool
    {
        // Seul l'organisateur de l'événement peut le supprimer
        return $event->getOrganizer() === $user;
    }

    private function canParticipate(Event $event, UserInterface $user): bool
    {
        // Un utilisateur connecté peut participer à un événement s'il n'en est pas l'organisateur
        // et s'il n'y participe pas déjà
        if ($event->getOrganizer() === $user) {
            return false; // L'organisateur ne peut pas participer à son propre événement
        }

        // Vérifier si l'utilisateur participe déjà
        $isAlreadyParticipating = $event->getParticipants()->exists(function ($key, $participation) use ($user) {
            return $participation->getUser() === $user;
        });

        return !$isAlreadyParticipating;
    }
}
