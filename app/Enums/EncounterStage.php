<?php

namespace App\Enums;

enum EncounterStage: string
{
    case Registration    = 'registration';
    case Triage          = 'triage';
    case Screening       = 'screening';
    case Lab             = 'lab';
    case ScreeningReview = 'screening_review';
    case Pharmacy        = 'pharmacy';
    case Completed       = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Registration    => 'Registration',
            self::Triage          => 'Triage',
            self::Screening       => 'Screening',
            self::Lab             => 'Lab',
            self::ScreeningReview => 'Screening Review',
            self::Pharmacy        => 'Pharmacy',
            self::Completed       => 'Completed',
        };
    }

    /**
     * Position in the standard encounter pipeline.
     * Screening_review and pharmacy share a branching path
     * (lab path adds 2 extra steps) so we reflect that here.
     */
    public function sequence(): int
    {
        return match ($this) {
            self::Registration    => 1,
            self::Triage          => 2,
            self::Screening       => 3,
            self::Lab             => 4,
            self::ScreeningReview => 5,
            self::Pharmacy        => 6,
            self::Completed       => 7,
        };
    }

    public function isTerminal(): bool
    {
        return $this === self::Completed;
    }

    /** All stages that represent active clinical work (not final). */
    public static function activeStages(): array
    {
        return [
            self::Registration,
            self::Triage,
            self::Screening,
            self::Lab,
            self::ScreeningReview,
            self::Pharmacy,
        ];
    }
}
