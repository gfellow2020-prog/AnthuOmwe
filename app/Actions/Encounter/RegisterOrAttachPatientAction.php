<?php

namespace App\Actions\Encounter;

use App\Models\Patient;
use App\Support\TdltsBarcodeGenerator;
use Illuminate\Support\Facades\DB;

/**
 * Finds an existing patient or creates a new one.
 *
 * Returns:
 *   [
 *     'patient'      => Patient,
 *     'was_existing' => bool,
 *   ]
 */
class RegisterOrAttachPatientAction
{
    /**
     * @param  array{
     *   patient_id?: int|null,
     *   full_name?: string,
     *   gender?: string|null,
     *   date_of_birth?: string|null,
     *   nrc_number?: string|null,
     *   phone_number?: string|null,
     *   email?: string|null,
    *   create_household?: bool|null,
    *   household_id?: string|null,
    *   village?: string|null,
     * } $data
     * @return array{patient: Patient, was_existing: bool}
     */
    public function handle(array $data): array
    {
        // Use existing patient when their integer PK is supplied
        if (! empty($data['patient_id'])) {
            $patient = Patient::findOrFail((int) $data['patient_id']);
            return ['patient' => $patient, 'was_existing' => true];
        }

        $createHousehold = (bool) ($data['create_household'] ?? false);
        $householdId = null;
        $householdHead = null;
        $householdVillage = null;

        if ($createHousehold) {
            $householdId = $this->generateHouseholdId();
            $householdHead = trim((string) ($data['full_name'] ?? ''));
            $householdVillage = isset($data['village']) ? trim((string) $data['village']) : null;
            $householdVillage = $householdVillage !== '' ? $householdVillage : null;

            DB::table('households')->insert([
                'household_id'   => $householdId,
                'head_of_house'  => $householdHead !== '' ? $householdHead : null,
                'nrc_number'     => $data['nrc_number'] ?? null,
                'phone_number'   => $data['phone_number'] ?? null,
                'village'        => $householdVillage,
                'barcode'        => TdltsBarcodeGenerator::generate('H', $householdId),
                'payment_status' => 'Active',
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        } elseif (!empty($data['household_id'])) {
            $selectedHousehold = DB::table('households')
                ->select('household_id', 'head_of_house', 'village')
                ->where('household_id', (string) $data['household_id'])
                ->first();

            if ($selectedHousehold) {
                $householdId = (string) $selectedHousehold->household_id;
                $householdHead = (string) ($selectedHousehold->head_of_house ?? '');
                $householdVillage = (string) ($selectedHousehold->village ?? '');
                $householdVillage = $householdVillage !== '' ? $householdVillage : null;
            }
        }

        // Create a new patient record
        $patient = Patient::create([
            'patient_id'   => $this->generatePatientNumber(),
            'full_name'    => $data['full_name'],
            'gender'       => $data['gender']        ?? null,
            'date_of_birth'=> $data['date_of_birth'] ?? null,
            'nrc_number'   => $data['nrc_number']    ?? null,
            'phone_number' => $data['phone_number']  ?? null,
            'email'        => $data['email']         ?? null,
            'city_town_village'      => $householdVillage,
            'relationship_to_head'    => $createHousehold ? 'Head' : ($householdId ? 'Member' : null),
            'household_head_of_house' => $householdHead !== '' ? $householdHead : null,
            'household_id'            => $householdId,
        ]);

        return ['patient' => $patient, 'was_existing' => false];
    }

    private function generatePatientNumber(): string
    {
        // Reuse the existing barcode generator for patient numbers (P prefix)
        $latest = \App\Models\Patient::orderBy('id', 'desc')->first();
        $nextId = $latest ? ($latest->id + 1) : 1;
        return TdltsBarcodeGenerator::generate('P', $nextId);
    }

    private function generateHouseholdId(): string
    {
        do {
            $candidate = 'HH-' . now()->format('Ymd') . '-' . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (DB::table('households')->where('household_id', $candidate)->exists());

        return $candidate;
    }
}
