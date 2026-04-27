<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalendarEvent extends Model
{
    use HasFactory;
    protected $fillable = [
        'title',
        'description',
        'event_date',
        'start_time',
        'end_time',
        'event_type',
        'location',
        'created_by',
    ];

    protected $casts = [
        'event_date' => 'date',
    ];

    public static array $types = [
        'appointment' => 'Appointment',
        'meeting'     => 'Meeting',
        'reminder'    => 'Reminder',
        'other'       => 'Other',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ─── Scopes ───────────────────────────────────────────────────────────────

    public function scopeForMonth($query, int $year, int $month)
    {
        return $query->whereYear('event_date', $year)->whereMonth('event_date', $month);
    }

    public function scopeUpcoming($query, int $limit = 5)
    {
        return $query->where('event_date', '>=', now()->toDateString())
                     ->orderBy('event_date')
                     ->orderBy('start_time')
                     ->limit($limit);
    }
}
