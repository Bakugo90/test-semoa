<?php

namespace App\DTO;

use App\Domain\Enum\TicketPriority;
use Symfony\Component\Validator\Constraints as Assert;

class TicketCreateDTO
{
    #[Assert\NotBlank(message: 'Le titre est requis')]
    #[Assert\Length(min: 3, max: 255, minMessage: 'Le titre doit contenir au moins 3 caractères')]
    public string $title;

    #[Assert\NotBlank(message: 'La description est requise')]
    public string $description;

    #[Assert\Choice(choices: ['LOW', 'MEDIUM', 'HIGH'], message: 'Priorité invalide')]
    public ?string $priority = null;
}
