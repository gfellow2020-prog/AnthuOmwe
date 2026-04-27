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
        'item_per_dose',
        'frequency',
        'time_per',
        'frequency_unit',
        'duration',
        'duration_unit',
        'start_date',
        'end_date',
        'quantity_prescribed',
        'route',
        'is_passer_by',
        'instructions',
    ];

    protected $casts = [
        'quantity_prescribed' => 'integer',
        'drug_id'             => 'integer',
        'item_per_dose'       => 'integer',
        'is_passer_by'        => 'boolean',
        'start_date'          => 'date',
        'end_date'            => 'date',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(PharmacyPrescription::class, 'pharmacy_prescription_id');
    }
}
