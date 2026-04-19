<?php

namespace App\Models;

use App\Enums\QueueTransitionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterStageLog extends Model
{
    protected $fillable = [
        'encounter_id',
        'patient_id',
        'stage_name',
        'stage_sequence',
        'status',
        'started_by',
        'completed_by',
        'started_at',
        'completed_at',
        'notes',
        'metadata',
    ];

    protected $casts = [
        'status'       => QueueTransitionStatus::class,
        'started_at'   => 'datetime',
        'completed_at' => 'datetime',
        'metadata'     => 'array',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function completedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isComplete(): bool
    {
        return $this->status === QueueTransitionStatus::Completed;
    }
}
