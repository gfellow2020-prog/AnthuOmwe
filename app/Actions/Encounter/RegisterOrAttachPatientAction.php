<?php

namespace App\Actions\Encounter;

use App\Models\Patient;
use App\Support\TdltsBarcodeGenerator;

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

        // Create a new patient record
        $patient = Patient::create([
            'patient_id'   => $this->generatePatientNumber(),
            'full_name'    => $data['full_name'],
            'gender'       => $data['gender']        ?? null,
            'date_of_birth'=> $data['date_of_birth'] ?? null,
            'nrc_number'   => $data['nrc_number']    ?? null,
            'phone_number' => $data['phone_number']  ?? null,
            'email'        => $data['email']         ?? null,
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
}
