<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'patient_id' => $this->patient_id,
            'barcode' => $this->barcode,
            'full_name' => $this->full_name,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth?->format('Y-m-d'),
            'nrc_number' => $this->nrc_number,
            'phone_number' => $this->phone_number,
            'email' => $this->email,
            'household_id' => $this->household_id,
            'household_head_of_house' => $this->household_head_of_house,
            'allergies' => $this->allergies,
            'active_encounter' => new EncounterSummaryResource($this->whenLoaded('activeEncounter')),
            'encounters' => EncounterSummaryResource::collection($this->whenLoaded('encounters')),
        ];
    }
}
