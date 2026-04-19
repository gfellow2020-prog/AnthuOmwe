<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResult extends Model
{
    protected $fillable = [
        'lab_request_id',
        'lab_request_item_id',
        'encounter_id',
        'patient_id',
        'recorded_by',
        'verified_by',
        'result_value',
        'result_text',
        'reference_range',
        'interpretation',
        'remarks',
        'result_status',
        'result_recorded_at',
        'verified_at',
    ];

    protected $casts = [
        'result_recorded_at' => 'datetime',
        'verified_at'        => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    public function labRequestItem(): BelongsTo
    {
        return $this->belongsTo(LabRequestItem::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    public function isAbnormal(): bool
    {
        return in_array($this->interpretation, ['abnormal', 'critical'], strict: true);
    }
}
