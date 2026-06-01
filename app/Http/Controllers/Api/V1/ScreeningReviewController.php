<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Encounter\CreatePrescriptionAction;
use App\Actions\Encounter\QueueEncounterToPharmacyAction;
use App\Actions\Encounter\ReceiveScreeningReviewQueueAction;
use App\Actions\Encounter\RecordScreeningReviewAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScreeningReviewRequest;
use App\Http\Resources\Api\V1\EncounterSummaryResource;
use App\Models\Encounter;
use Illuminate\Http\JsonResponse;

class ScreeningReviewController extends Controller
{
    public function receive(Encounter $encounter, ReceiveScreeningReviewQueueAction $receiveAction): JsonResponse
    {
        $receiveAction->handle($encounter, auth()->id());
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} received into Screening Review.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }

    public function complete(
        ScreeningReviewRequest $request,
        Encounter $encounter,
        RecordScreeningReviewAction $reviewAction,
        CreatePrescriptionAction $prescriptionAction,
        QueueEncounterToPharmacyAction $pharmacyAction,
    ): JsonResponse {
        $data = $request->validated();

        $reviewRecord = $reviewAction->handle($encounter, [
            'final_diagnosis' => $data['final_diagnosis'],
            'clinical_findings' => $data['clinical_findings'] ?? null,
            'physical_examination' => $data['physical_examination'] ?? null,
            'assessment_notes' => $data['assessment_notes'] ?? null,
            'plan' => $data['plan'] ?? null,
            'review_notes' => $data['review_notes'] ?? null,
        ], auth()->id());

        $prescriptionAction->handle(
            encounter: $encounter,
            data: [
                'notes' => $data['prescription_notes'] ?? null,
                'items' => $data['items'],
            ],
            prescribedById: auth()->id(),
            screeningRecord: $reviewRecord,
        );

        $pharmacyAction->handle($encounter->refresh(), auth()->id());
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} queued to Pharmacy.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }
}
