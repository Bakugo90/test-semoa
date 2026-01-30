<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class TicketUpdateDTO
{
    #[Assert\NotBlank(message: "Le status est obligatoire")]
    #[Assert\Choice(choices: ['OPEN', 'IN_PROGRESS', 'DONE'], message: "Status invalide")]
    public string $status;
}
