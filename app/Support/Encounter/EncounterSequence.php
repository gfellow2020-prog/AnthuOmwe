<?php

namespace App\Support\Encounter;

use App\Enums\EncounterStage;

/**
 * Provides ordering utilities for encounter stages.
 * Complements EncounterStageMap (which handles legal transitions)
 * with numeric position comparisons.
 */
class EncounterSequence
{
    public static function of(EncounterStage $stage): int
    {
        return $stage->sequence();
    }

    public static function isBefore(EncounterStage $stage, EncounterStage $reference): bool
    {
        return $stage->sequence() < $reference->sequence();
    }

    public static function isAfter(EncounterStage $stage, EncounterStage $reference): bool
    {
        return $stage->sequence() > $reference->sequence();
    }

    public static function isAtOrBefore(EncounterStage $stage, EncounterStage $reference): bool
    {
        return $stage->sequence() <= $reference->sequence();
    }

    public static function isAtOrAfter(EncounterStage $stage, EncounterStage $reference): bool
    {
        return $stage->sequence() >= $reference->sequence();
    }

    /**
     * Returns all stages sorted by sequence position (ascending).
     *
     * @return EncounterStage[]
     */
    public static function allInOrder(): array
    {
        $stages = EncounterStage::cases();
        usort($stages, static fn (EncounterStage $a, EncounterStage $b) => $a->sequence() <=> $b->sequence());
        return $stages;
    }

    /**
     * Returns all stages up to and including the given stage.
     *
     * @return EncounterStage[]
     */
    public static function upTo(EncounterStage $upTo): array
    {
        return array_filter(
            self::allInOrder(),
            static fn (EncounterStage $s) => $s->sequence() <= $upTo->sequence(),
        );
    }
}
