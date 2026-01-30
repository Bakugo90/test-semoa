<?php

namespace App\Domain\Enum;

enum TicketStatus: string
{
    case OPEN = 'OPEN';
    case IN_PROGRESS = 'IN_PROGRESS';
    case DONE = 'DONE';

    public function canTransitionTo(TicketStatus $newStatus): bool
    {
        return match($this) {
            self::OPEN => in_array($newStatus, [self::IN_PROGRESS, self::DONE]),
            self::IN_PROGRESS => $newStatus === self::DONE,
            self::DONE => false,
        };
    }
}
