<?php

namespace App\Traits;

use App\Models\Encounter;
use App\Services\Encounter\EncounterLockService;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Attaches model observers that block saves and deletes when the
 * parent encounter is locked.
 *
 * Usage: add `use LocksEncounterRecords;` to any stage-specific model
 * (RegistrationRecord, TriageRecord, ScreeningRecord, etc.).
 *
 * The consuming model MUST implement `encounter(): BelongsTo`.
 */
trait LocksEncounterRecords
{
    protected static function bootLocksEncounterRecords(): void
    {
        $guard = static function (self $model): void {
            // Only enforce the lock for records that already exist in the DB.
            // New records are created before the encounter is locked.
            if (! $model->exists) {
                return;
            }

            $encounter = $model->encounter;

            if ($encounter instanceof Encounter) {
                /** @var EncounterLockService $service */
                $service = app(EncounterLockService::class);
                $service->assertNotLocked($encounter);
            }
        };

        static::updating($guard);
        static::deleting($guard);
    }

    /**
     * Concrete model must define this relationship.
     */
    abstract public function encounter(): BelongsTo;
}
