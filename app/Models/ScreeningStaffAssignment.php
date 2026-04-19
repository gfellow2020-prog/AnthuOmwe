<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScreeningStaffAssignment extends Model
{
    protected $fillable = [
        'screening_record_id',
        'user_id',
        'role_name',
        'participation_type',
        'notes',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function screeningRecord(): BelongsTo
    {
        return $this->belongsTo(ScreeningRecord::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
