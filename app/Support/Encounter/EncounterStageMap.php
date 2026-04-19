<?php

namespace App\Support\Encounter;

use App\Enums\EncounterStage;
use App\Exceptions\Encounter\InvalidEncounterTransitionException;

/**
 * Defines the legal stage transition graph for the encounter pipeline.
 *
 * Standard path:     Registration → Triage → Screening → Lab → ScreeningReview → Pharmacy → Completed
 * Short-circuit path: Registration → Triage → Screening → Pharmacy → Completed  (no lab)
 */
class EncounterStageMap
{
    /**
     * Returns all stages that may legally follow the given stage.
     *
     * @return EncounterStage[]
     */
    public static function validNextStages(EncounterStage $stage): array
    {
        return match ($stage) {
            EncounterStage::Registration    => [EncounterStage::Triage],
            EncounterStage::Triage          => [EncounterStage::Screening],
            EncounterStage::Screening       => [EncounterStage::Lab, EncounterStage::Pharmacy],
            EncounterStage::Lab             => [EncounterStage::ScreeningReview],
            EncounterStage::ScreeningReview => [EncounterStage::Pharmacy],
            EncounterStage::Pharmacy        => [EncounterStage::Completed],
            EncounterStage::Completed       => [],
        };
    }

    /**
     * Returns true if the from→to transition is legal.
     */
    public static function canTransitionTo(EncounterStage $from, EncounterStage $to): bool
    {
        return in_array($to, self::validNextStages($from), strict: true);
    }

    /**
     * Throws InvalidEncounterTransitionException if the transition is not allowed.
     */
    public static function assertCanTransitionTo(EncounterStage $from, EncounterStage $to): void
    {
        if (! self::canTransitionTo($from, $to)) {
            throw new InvalidEncounterTransitionException($from, $to);
        }
    }

    /**
     * Returns all stages that may precede the given stage.
     * Useful for validating that a department only receives from the right source.
     *
     * @return EncounterStage[]
     */
    public static function validPriorStages(EncounterStage $stage): array
    {
        return match ($stage) {
            EncounterStage::Registration    => [],
            EncounterStage::Triage          => [EncounterStage::Registration],
            EncounterStage::Screening       => [EncounterStage::Triage],
            EncounterStage::Lab             => [EncounterStage::Screening],
            EncounterStage::ScreeningReview => [EncounterStage::Lab],
            EncounterStage::Pharmacy        => [EncounterStage::Screening, EncounterStage::ScreeningReview],
            EncounterStage::Completed       => [EncounterStage::Pharmacy],
        };
    }
}
