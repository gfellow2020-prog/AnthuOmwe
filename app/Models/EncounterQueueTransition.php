<?php

namespace App\Models;

use App\Enums\QueueTransitionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterQueueTransition extends Model
{
    protected $fillable = [
        'encounter_id',
        'patient_id',
        'from_stage',
        'to_stage',
        'queued_by',
        'received_by',
        'queued_at',
        'received_at',
        'transition_notes',
        'status',
    ];

    protected $casts = [
        'status'      => QueueTransitionStatus::class,
        'queued_at'   => 'datetime',
        'received_at' => 'datetime',
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

    public function queuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'queued_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isOpen(): bool
    {
        return $this->status->isOpen();
    }

    public function markReceived(int $userId): void
    {
        $this->update([
            'received_by'  => $userId,
            'received_at'  => now(),
            'status'       => QueueTransitionStatus::Received,
        ]);
    }

    public function markCompleted(): void
    {
        $this->update(['status' => QueueTransitionStatus::Completed]);
    }
}
