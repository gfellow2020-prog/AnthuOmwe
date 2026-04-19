<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabSample extends Model
{
    protected $fillable = [
        'lab_request_id',
        'encounter_id',
        'patient_id',
        'collected_by',
        'sample_type',
        'sample_label',
        'collection_notes',
        'collected_at',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function collectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'collected_by');
    }
}
