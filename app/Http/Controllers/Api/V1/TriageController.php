<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Encounter\QueueEncounterToScreeningAction;
use App\Actions\Encounter\ReceiveTriageQueueAction;
use App\Actions\Encounter\RecordTriageAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\TriageRequest;
use App\Http\Resources\Api\V1\EncounterDetailResource;
use App\Http\Resources\Api\V1\EncounterSummaryResource;
use App\Models\Encounter;
use App\Models\Medication;
use App\Models\StartupMedication;
use App\Services\Encounter\EncounterDetailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TriageController extends Controller
{
    public function receive(
        Encounter $encounter,
        ReceiveTriageQueueAction $receiveAction,
    ): JsonResponse {
        $receiveAction->handle($encounter, auth()->id());
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} received into Triage.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }

    public function saveVitals(
        TriageRequest $request,
        Encounter $encounter,
        RecordTriageAction $recordAction,
        EncounterDetailService $detailService,
    ): JsonResponse {
        $recordAction->handle($encounter, $request->validated(), auth()->id());

        return response()->json([
            'message' => 'Vitals saved.',
            'data' => new EncounterDetailResource($detailService->load($encounter->refresh())),
        ]);
    }

    public function complete(
        TriageRequest $request,
        Encounter $encounter,
        RecordTriageAction $recordAction,
        QueueEncounterToScreeningAction $queueAction,
    ): JsonResponse {
        $recordAction->handle($encounter, $request->validated(), auth()->id());
        $queueAction->handle($encounter->refresh(), auth()->id(), $request->input('notes'));
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} queued to Screening.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }

    public function storeStartupMedication(Request $request, Encounter $encounter): JsonResponse
    {
        $validated = $request->validate([
            'medication_id' => ['nullable', 'exists:medications,id'],
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['nullable', 'string', 'max:100'],
            'route' => ['nullable', 'string', 'max:50'],
            'frequency' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'administered_at' => ['nullable', 'date'],
        ]);

        $medication = $encounter->startupMedications()->create($validated + [
            'triage_record_id' => $encounter->triageRecord?->id,
            'patient_id' => $encounter->patient_id,
            'recorded_by' => auth()->id(),
        ]);

        return response()->json([
            'message' => 'Startup medication recorded.',
            'data' => $medication->load('recordedBy'),
        ], 201);
    }

    public function destroyStartupMedication(StartupMedication $medication): JsonResponse
    {
        $medication->delete();

        return response()->json([
            'message' => 'Startup medication removed.',
        ]);
    }

    public function searchMedications(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $q = $validated['q'] ?? '';

        $results = Medication::active()
            ->where(function ($query) use ($q): void {
                $query->where('name', 'like', "%{$q}%")
                    ->orWhere('generic_name', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'generic_name', 'strength', 'form', 'category', 'default_route', 'default_frequency']);

        return response()->json(['data' => $results]);
    }
}
