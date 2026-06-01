<?php

namespace App\Http\Resources\Api\V1;

use BackedEnum;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EncounterDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'encounter_number' => $this->encounter_number,
            'visit_type' => $this->visit_type,
            'priority_level' => $this->priority_level,
            'current_stage' => $this->enumValue($this->current_stage),
            'current_status' => $this->enumValue($this->current_status),
            'started_at' => $this->started_at?->toIso8601String(),
            'closed_at' => $this->closed_at?->toIso8601String(),
            'closure_notes' => $this->closure_notes,
            'is_locked' => (bool) $this->is_locked,
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'started_by' => new UserResource($this->whenLoaded('startedBy')),
            'closed_by' => new UserResource($this->whenLoaded('closedBy')),
            'registration_record' => $this->whenLoaded('registrationRecord'),
            'triage_record' => $this->whenLoaded('triageRecord'),
            'screening_record' => $this->whenLoaded('screeningRecord'),
            'screening_review_record' => $this->whenLoaded('screeningReviewRecord'),
            'lab_request' => $this->whenLoaded('labRequest'),
            'prescription' => $this->whenLoaded('prescription'),
            'dispense' => $this->whenLoaded('dispense'),
            'stage_logs' => $this->whenLoaded('stageLogs'),
            'queue_transitions' => $this->whenLoaded('queueTransitions'),
            'audits' => $this->whenLoaded('audits'),
        ];
    }

    private function enumValue(mixed $value): mixed
    {
        return $value instanceof BackedEnum ? $value->value : $value;
    }
}
