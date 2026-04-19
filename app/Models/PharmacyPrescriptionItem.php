<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyPrescriptionItem extends Model
{
    protected $fillable = [
        'pharmacy_prescription_id',
        'drug_id',
        'drug_name',
        'strength',
        'formulation',
        'dose',
        'frequency',
        'duration',
        'quantity_prescribed',
        'route',
        'instructions',
    ];

    protected $casts = [
        'quantity_prescribed' => 'integer',
        'drug_id'             => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(PharmacyPrescription::class, 'pharmacy_prescription_id');
    }
}
