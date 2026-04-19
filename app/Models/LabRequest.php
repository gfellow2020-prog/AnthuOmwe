<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabRequest extends Model
{
    protected $fillable = [
        'encounter_id',
        'patient_id',
        'screening_record_id',
        'requested_by',
        'request_number',
        'request_notes',
        'priority_level',
        'status',
        'requested_at',
        'completed_at',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'completed_at' => 'datetime',
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

    public function screeningRecord(): BelongsTo
    {
        return $this->belongsTo(ScreeningRecord::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(LabRequestItem::class);
    }

    public function samples(): HasMany
    {
        return $this->hasMany(LabSample::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isPending(): bool   { return $this->status === 'pending'; }
    public function isCompleted(): bool { return $this->status === 'completed'; }

    public function hasResults(): bool
    {
        return $this->results()->exists();
    }
}
