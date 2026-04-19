<?php

namespace App\Exceptions\Encounter;

use App\Enums\EncounterStage;
use RuntimeException;

class InvalidEncounterTransitionException extends RuntimeException
{
    public function __construct(EncounterStage $from, EncounterStage $to)
    {
        parent::__construct(
            "Cannot transition encounter from [{$from->value}] to [{$to->value}]. Invalid stage transition."
        );
    }
}
