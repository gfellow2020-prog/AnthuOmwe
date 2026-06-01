<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Encounter\QueueEncounterToTriageAction;
use App\Actions\Encounter\StartEncounterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StartEncounterRequest;
use App\Http\Resources\Api\V1\EncounterDetailResource;
use App\Http\Resources\Api\V1\EncounterSummaryResource;
use App\Models\Encounter;
use App\Services\Encounter\EncounterDetailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EncounterController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'stage' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string', 'max:50'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Encounter::with(['patient', 'startedBy'])->latest('started_at');

        if ($request->filled('stage')) {
            $query->where('current_stage', $request->query('stage'));
        }

        if ($request->filled('status')) {
            $query->where('current_status', $request->query('status'));
        }

        return EncounterSummaryResource::collection(
            $query->paginate((int) $request->integer('per_page', 25)),
        );
    }

    public function store(StartEncounterRequest $request, StartEncounterAction $startAction): JsonResponse
    {
        $encounter = $startAction->handle(
            data: $request->validated(),
            registrarId: $request->user()->id,
        )->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} started.",
            'data' => new EncounterSummaryResource($encounter),
        ], 201);
    }

    public function show(Encounter $encounter, EncounterDetailService $detailService): EncounterDetailResource
    {
        return new EncounterDetailResource($detailService->load($encounter));
    }

    public function queueToTriage(
        Request $request,
        Encounter $encounter,
        QueueEncounterToTriageAction $queueAction,
    ): JsonResponse {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $queueAction->handle($encounter, $request->user()->id, $validated['notes'] ?? null);
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} queued to Triage.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }
}
