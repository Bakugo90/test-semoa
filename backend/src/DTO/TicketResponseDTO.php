<?php

namespace App\DTO;

use App\Domain\Entity\Ticket;


class TicketResponseDTO
{
    public string $id;
    public string $title;
    public string $description;
    public string $status;
    public string $priority;
    public string $createdBy;
    public string $createdAt;
    public ?string $updatedAt;

    public static function fromEntity(Ticket $ticket): self
    {
        $dto = new self();
        $dto->id = $ticket->getId();
        $dto->title = $ticket->getTitle();
        $dto->description = $ticket->getDescription();
        $dto->status = $ticket->getStatus()->value;
        $dto->priority = $ticket->getPriority()->value;
        $dto->createdBy = $ticket->getCreatedBy()->getId();
        $dto->createdAt = $ticket->getCreatedAt()->format('Y-m-d H:i:s');
        $dto->updatedAt = $ticket->getUpdatedAt()?->format('Y-m-d H:i:s');
        
        return $dto;
    }
}
