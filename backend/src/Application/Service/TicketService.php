<?php

namespace App\Application\Service;

use App\Domain\Entity\Ticket;
use App\Domain\Entity\User;
use App\Domain\Enum\TicketPriority;
use App\Domain\Enum\TicketStatus;
use App\Infrastructure\Repository\TicketRepository;

class TicketService
{
    public function __construct(
        private TicketRepository $ticketRepository
    ) {}

    public function createTicket(string $title, string $description, ?string $priority, User $user): Ticket
    {
        $priorityEnum = $priority ? TicketPriority::from($priority) : TicketPriority::MEDIUM;
        
        $ticket = new Ticket();
        $ticket->setTitle($title);
        $ticket->setDescription($description);
        $ticket->setPriority($priorityEnum);
        $ticket->setCreatedBy($user);
        
        $this->ticketRepository->save($ticket);
        
        return $ticket;
    }

    public function getTicketById(string $id, User $user): ?Ticket
    {
        $ticket = $this->ticketRepository->find($id);

        if (!$ticket || $ticket->getCreatedBy()->getId() !== $user->getId()) {
            return null;
        }

        return $ticket;
    }

    public function findUserTickets(User $user, ?TicketStatus $status = null, ?TicketPriority $priority = null, int $page = 1, int $limit = 10, string $sortBy = 'createdAt', string $order = 'DESC'): array
    {
        return $this->ticketRepository->findByUser($user, $status, $priority, $page, $limit, $sortBy, $order);
    }

    public function countUserTickets(User $user, ?TicketStatus $status = null, ?TicketPriority $priority = null): int
    {
        return $this->ticketRepository->countByUser($user, $status, $priority);
    }

    public function updateTicketStatus(Ticket $ticket, string $newStatus): Ticket
    {
        $newStatusEnum = TicketStatus::from($newStatus);

        if (!$ticket->getStatus()->canTransitionTo($newStatusEnum)) {
            throw new \RuntimeException('Transition de status invalide');
        }

        $ticket->setStatus($newStatusEnum);
        $this->ticketRepository->save($ticket);

        return $ticket;
    }

    public function deleteTicket(Ticket $ticket): void
    {
        $this->ticketRepository->remove($ticket);
    }

    public function canUserModifyTicket(Ticket $ticket, User $user): bool
    {
        // Un utilisateur peut modifier son propre ticket, ou un admin peut modifier n'importe quel ticket
        return $ticket->getCreatedBy()->getId() === $user->getId() 
            || in_array('ROLE_ADMIN', $user->getRoles());
    }
}
