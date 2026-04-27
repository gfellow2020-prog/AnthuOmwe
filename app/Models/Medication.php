<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Medication extends Model
{
    protected $fillable = [
        'name',
        'generic_name',
        'category',
        'form',
        'strength',
        'default_route',
        'default_frequency',
        'is_controlled',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'is_controlled' => 'boolean',
        'is_active'     => 'boolean',
    ];

    // ─── Scopes ───────────────────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function startupMedications(): HasMany
    {
        return $this->hasMany(StartupMedication::class);
    }

    // ─── Display helpers ──────────────────────────────────────────────────

    public function displayName(): string
    {
        $parts = [$this->name];
        if ($this->strength) {
            $parts[] = $this->strength;
        }
        if ($this->form) {
            $parts[] = '(' . $this->form . ')';
        }
        return implode(' ', $parts);
    }
}
