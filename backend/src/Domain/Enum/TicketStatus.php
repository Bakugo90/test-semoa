<?php

namespace App\Domain\Enum;

enum TicketStatus: string
{
    case OPEN = 'OPEN';
    case IN_PROGRESS = 'IN_PROGRESS';
    case RESOLVED = 'RESOLVED';
    case CLOSED = 'CLOSED';

    public function canTransitionTo(TicketStatus $newStatus): bool
    {
        return match($this) {
            self::OPEN => $newStatus === self::IN_PROGRESS,
            self::IN_PROGRESS => $newStatus === self::RESOLVED,
            self::RESOLVED => $newStatus === self::CLOSED,
            self::CLOSED => false,
        };
    }
}
