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
    public function handle(string $query, ?string $dateOfBirth = null, ?string $sex = null): Collection
    {
        $q = trim($query);
        $nameTokens = array_values(array_filter(preg_split('/\s+/', $q)));
        $normalizedSex = $sex ? strtolower(trim($sex)) : null;

        $builder = Patient::query();

        if ($normalizedSex) {
            $builder->whereRaw('LOWER(gender) = ?', [$normalizedSex]);
        }

        if ($dateOfBirth) {
            // Name + DOB search — narrow match
            return $builder
                ->where('date_of_birth', $dateOfBirth)
                ->where(function ($sub) use ($q, $nameTokens) {
                    $sub->where('full_name', 'like', "%{$q}%")
                        ->orWhere(function ($nameSub) use ($nameTokens) {
                            foreach ($nameTokens as $token) {
                                $nameSub->where('full_name', 'like', "%{$token}%");
                            }
                        })
                        ->orWhere('patient_id', $q);
                })
                ->limit(20)
                ->get();
        }

        return $builder->where(function ($sub) use ($q, $nameTokens) {
            $sub->where('patient_id', $q)
                ->orWhere('barcode', $q)
                ->orWhere('nrc_number', 'like', "%{$q}%")
                ->orWhere('phone_number', 'like', "%{$q}%")
                ->orWhere('other_cellphone', 'like', "%{$q}%")
                ->orWhere('landline', 'like', "%{$q}%")
                ->orWhere('art_number', 'like', "%{$q}%")
                ->orWhere('nupn', 'like', "%{$q}%")
                ->orWhere('full_name', 'like', "%{$q}%")
                ->orWhere(function ($nameSub) use ($nameTokens) {
                    foreach ($nameTokens as $token) {
                        $nameSub->where('full_name', 'like', "%{$token}%");
                    }
                });
        })
        ->limit(20)
        ->get();
    }
}
