<?php

namespace App\Enums;

enum QueueTransitionStatus: string
{
    case Queued    = 'queued';
    case Received  = 'received';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Queued    => 'Queued',
            self::Received  => 'Received',
            self::Completed => 'Completed',
        };
    }

    public function isOpen(): bool
    {
        return in_array($this, [self::Queued, self::Received]);
    }
}
