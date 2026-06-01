<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Encounter\CloseEncounterAction;
use App\Actions\Encounter\DispenseMedicationAction;
use App\Actions\Encounter\ReceivePharmacyQueueAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\CloseEncounterRequest;
use App\Http\Requests\DispenseMedicationRequest;
use App\Http\Resources\Api\V1\EncounterDetailResource;
use App\Http\Resources\Api\V1\EncounterSummaryResource;
use App\Models\Encounter;
use App\Services\Encounter\EncounterDetailService;
use Illuminate\Http\JsonResponse;

class PharmacyController extends Controller
{
    public function receive(Encounter $encounter, ReceivePharmacyQueueAction $receiveAction): JsonResponse
    {
        $receiveAction->handle($encounter, auth()->id());
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} received into Pharmacy.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }

    public function dispense(
        DispenseMedicationRequest $request,
        Encounter $encounter,
        DispenseMedicationAction $dispenseAction,
        EncounterDetailService $detailService,
    ): JsonResponse {
        $dispenseAction->handle($encounter, $request->validated(), auth()->id());

        return response()->json([
            'message' => 'Medications dispensed.',
            'data' => new EncounterDetailResource($detailService->load($encounter->refresh())),
        ]);
    }

    public function close(
        CloseEncounterRequest $request,
        Encounter $encounter,
        CloseEncounterAction $closeAction,
    ): JsonResponse {
        $closeAction->handle($encounter, auth()->id(), $request->validated()['closure_notes'] ?? null);
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => 'Encounter closed and locked.',
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }
}
