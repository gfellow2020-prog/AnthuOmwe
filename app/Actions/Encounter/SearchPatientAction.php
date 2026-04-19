<?php

namespace App\Actions\Encounter;

use App\Models\Patient;
use Illuminate\Support\Collection;

/**
 * Searches for an existing patient using multiple identifiers.
 * Returns a collection of matching Patient records (sorted by relevance).
 *
 * This action does NOT create patients — it only searches.
 */
class SearchPatientAction
{
    /**
     * Search patients by any of:
     *   - patient_id (patient number / barcode code)
     *   - nrc_number
     *   - phone_number
     *   - full_name (partial match, optionally combined with date_of_birth)
     *
     * @return Collection<int, Patient>
     */
    public function handle(string $query, ?string $dateOfBirth = null): Collection
    {
        $q = trim($query);

        $builder = Patient::query();

        if ($dateOfBirth) {
            // Name + DOB search — narrow match
            return $builder
                ->where('date_of_birth', $dateOfBirth)
                ->where(function ($sub) use ($q) {
                    $sub->where('full_name', 'like', "%{$q}%")
                        ->orWhere('patient_id', $q);
                })
                ->limit(20)
                ->get();
        }

        return $builder->where(function ($sub) use ($q) {
            $sub->where('patient_id', $q)
                ->orWhere('nrc_number', 'like', "%{$q}%")
                ->orWhere('phone_number', 'like', "%{$q}%")
                ->orWhere('other_cellphone', 'like', "%{$q}%")
                ->orWhere('landline', 'like', "%{$q}%")
                ->orWhere('art_number', 'like', "%{$q}%")
                ->orWhere('nupn', 'like', "%{$q}%")
                ->orWhere('full_name', 'like', "%{$q}%");
        })
        ->limit(20)
        ->get();
    }
}
