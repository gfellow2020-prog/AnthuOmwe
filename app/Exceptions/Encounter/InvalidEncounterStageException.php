<?php

namespace App\Exceptions\Encounter;

use App\Enums\EncounterStage;
use RuntimeException;

class InvalidEncounterStageException extends RuntimeException
{
    public function __construct(EncounterStage $expected, EncounterStage $actual)
    {
        parent::__construct(
            "Expected encounter to be at stage [{$expected->value}] but it is at [{$actual->value}]."
        );
    }
}
