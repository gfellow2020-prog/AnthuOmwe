<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PharmacyDispenseItem extends Model
{
    protected $fillable = [
        'pharmacy_dispense_id',
        'pharmacy_prescription_item_id',
        'drug_id',
        'drug_name',
        'quantity_dispensed',
        'batch_no',
        'stock_reference',
        'instructions',
    ];

    protected $casts = [
        'quantity_dispensed' => 'integer',
        'drug_id'            => 'integer',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function dispense(): BelongsTo
    {
        return $this->belongsTo(PharmacyDispense::class, 'pharmacy_dispense_id');
    }

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PharmacyPrescriptionItem::class, 'pharmacy_prescription_item_id');
    }
}
