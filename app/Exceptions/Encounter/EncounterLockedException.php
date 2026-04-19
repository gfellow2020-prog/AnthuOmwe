<?php

namespace App\Exceptions\Encounter;

use RuntimeException;

class EncounterLockedException extends RuntimeException
{
    public function __construct(string $encounterNumber)
    {
        parent::__construct(
            "Encounter [{$encounterNumber}] is locked and cannot be modified."
        );
    }
}
