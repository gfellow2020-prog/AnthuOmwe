<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Encounter\CreatePrescriptionAction;
use App\Actions\Encounter\QueueEncounterToLabAction;
use App\Actions\Encounter\QueueEncounterToPharmacyFromScreeningAction;
use App\Actions\Encounter\ReceiveScreeningQueueAction;
use App\Actions\Encounter\RecordInitialScreeningAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScreeningRequest;
use App\Http\Resources\Api\V1\EncounterSummaryResource;
use App\Models\Encounter;
use Illuminate\Http\JsonResponse;

class ScreeningController extends Controller
{
    public function receive(Encounter $encounter, ReceiveScreeningQueueAction $receiveAction): JsonResponse
    {
        $receiveAction->handle($encounter, auth()->id());
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} received into Screening.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }

    public function complete(
        ScreeningRequest $request,
        Encounter $encounter,
        RecordInitialScreeningAction $recordAction,
        CreatePrescriptionAction $prescriptionAction,
        QueueEncounterToLabAction $queueToLabAction,
        QueueEncounterToPharmacyFromScreeningAction $queueToPharmacyAction,
    ): JsonResponse {
        $data = $request->validated();

        $screeningRecord = $recordAction->handle($encounter, $data, auth()->id());

        $prescriptionItems = [];
        if (! empty($data['prescriptions'])) {
            $decoded = json_decode($data['prescriptions'], true);
            if (is_array($decoded) && count($decoded) > 0) {
                $prescriptionItems = $decoded;
            }
        }

        if ($prescriptionItems !== []) {
            $prescriptionAction->handle(
                encounter: $encounter,
                data: ['notes' => null, 'items' => $prescriptionItems],
                prescribedById: auth()->id(),
                screeningRecord: $screeningRecord,
            );
        }

        $encounter->refresh();
        $labRequested = (bool) ($data['lab_requested'] ?? false);

        if ($labRequested) {
            $queueToLabAction->handle($encounter, auth()->id(), $data['notes'] ?? null);
            $message = "Encounter {$encounter->encounter_number} queued to Lab.";
        } else {
            $queueToPharmacyAction->handle($encounter, auth()->id(), $data['notes'] ?? null);
            $message = "Encounter {$encounter->encounter_number} queued directly to Pharmacy.";
        }

        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => $message,
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }
}
