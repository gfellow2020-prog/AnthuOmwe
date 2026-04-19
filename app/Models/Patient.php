<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Wraps the existing `patients` table.
 *
 * Column mapping (existing column → guide concept):
 *   patient_id   → patient_number (human-readable unique code)
 *   full_name    → display name
 *   phone_number → phone
 *   nrc_number   → nrc
 *   allergies    → allergies_summary
 */
class Patient extends Model
{
    protected $table = 'patients';

    protected $fillable = [
        'patient_id',
        'full_name',
        'gender',
        'date_of_birth',
        'nrc_number',
        'phone_number',
        'email',
        'country',
        'other_cellphone',
        'landline',
        'house_number',
        'road_street',
        'area',
        'city_town_village',
        'landmarks',
        'marital_status',
        'spouse_first_name',
        'spouse_surname',
        'home_language',
        'born_in_zambia',
        'province_of_birth',
        'district_of_birth',
        'place_of_birth',
        'occupation',
        'art_number',
        'nupn',
        'blood_group',
        'allergies',
        'relationship_to_head',
        'household_head_of_house',
        'household_id',
        'barcode',
        'source_created_at',
    ];

    protected $casts = [
        'date_of_birth'    => 'date',
        'source_created_at' => 'datetime',
    ];

    // ─── Guide-compatible accessor aliases ───────────────────────────────────

    /** The human-readable patient identifier (maps to patient_id column). */
    public function getPatientNumberAttribute(): string
    {
        return $this->patient_id;
    }

    /** Display name alias. */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name;
    }

    /** Phone alias. */
    public function getPhoneAttribute(): ?string
    {
        return $this->phone_number;
    }

    /** NRC alias. */
    public function getNrcAttribute(): ?string
    {
        return $this->nrc_number;
    }

    /** Allergies summary alias. */
    public function getAllergiesSummaryAttribute(): ?string
    {
        return $this->allergies;
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function activeEncounter(): HasOne
    {
        return $this->hasOne(Encounter::class)
            ->whereNotIn('current_stage', ['completed'])
            ->whereNull('deleted_at')
            ->latest('started_at');
    }

    public function registrationRecords(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RegistrationRecord::class);
    }
}
