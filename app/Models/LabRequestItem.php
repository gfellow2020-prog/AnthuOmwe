<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabRequestItem extends Model
{
    protected $fillable = [
        'lab_request_id',
        'test_code',
        'test_name',
        'specimen_type',
        'test_group',
        'instructions',
        'status',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function labRequest(): BelongsTo
    {
        return $this->belongsTo(LabRequest::class);
    }

    public function results(): HasMany
    {
        return $this->hasMany(LabResult::class);
    }
}
