<?php

namespace App\Enums;

enum EncounterStatus: string
{
    case Started    = 'started';
    case Queued     = 'queued';
    case InProgress = 'in_progress';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Started    => 'Started',
            self::Queued     => 'Queued',
            self::InProgress => 'In Progress',
            self::Completed  => 'Completed',
            self::Cancelled  => 'Cancelled',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Started, self::Queued, self::InProgress]);
    }

    public function isClosed(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled]);
    }
}
