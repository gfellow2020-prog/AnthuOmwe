<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\Encounter\QueueEncounterBackToScreeningAction;
use App\Actions\Encounter\ReceiveLabQueueAction;
use App\Actions\Encounter\RecordLabResultsAction;
use App\Actions\Encounter\RecordLabSamplesAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\LabRequestStoreRequest;
use App\Http\Requests\LabResultStoreRequest;
use App\Http\Resources\Api\V1\EncounterDetailResource;
use App\Http\Resources\Api\V1\EncounterSummaryResource;
use App\Models\Encounter;
use App\Services\Encounter\EncounterDetailService;
use Illuminate\Http\JsonResponse;

class LabController extends Controller
{
    public function receive(Encounter $encounter, ReceiveLabQueueAction $receiveAction): JsonResponse
    {
        $receiveAction->handle($encounter, auth()->id());
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} received into Lab.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }

    public function samples(
        LabRequestStoreRequest $request,
        Encounter $encounter,
        RecordLabSamplesAction $samplesAction,
        EncounterDetailService $detailService,
    ): JsonResponse {
        $encounter->loadMissing('labRequest');

        if (! $encounter->labRequest) {
            abort(404, 'No lab request found. Receive the patient first.');
        }

        $data = $request->validated();

        if (! empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $encounter->labRequest->items()->create($item + ['status' => 'pending']);
            }
        }

        if (! empty($data['samples'])) {
            $samplesAction->handle(
                $encounter->labRequest,
                ['samples' => $data['samples']],
                auth()->id(),
            );
        }

        return response()->json([
            'message' => 'Sample collection recorded.',
            'data' => new EncounterDetailResource($detailService->load($encounter->refresh())),
        ]);
    }

    public function results(
        LabResultStoreRequest $request,
        Encounter $encounter,
        RecordLabResultsAction $resultsAction,
        EncounterDetailService $detailService,
    ): JsonResponse {
        $encounter->loadMissing('labRequest');

        if (! $encounter->labRequest) {
            abort(404, 'No lab request found.');
        }

        $resultsAction->handle(
            $encounter->labRequest,
            ['results' => $request->validated()['results']],
            auth()->id(),
        );

        return response()->json([
            'message' => 'Results recorded.',
            'data' => new EncounterDetailResource($detailService->load($encounter->refresh())),
        ]);
    }

    public function complete(
        LabResultStoreRequest $request,
        Encounter $encounter,
        RecordLabResultsAction $resultsAction,
        QueueEncounterBackToScreeningAction $completeAction,
    ): JsonResponse {
        $encounter->loadMissing('labRequest');

        if (! $encounter->labRequest) {
            abort(404, 'No lab request found.');
        }

        $resultsAction->handle(
            $encounter->labRequest,
            ['results' => $request->validated()['results']],
            auth()->id(),
        );

        $completeAction->handle($encounter->refresh(), auth()->id(), $request->input('notes'));
        $encounter->refresh()->load(['patient', 'startedBy']);

        return response()->json([
            'message' => "Encounter {$encounter->encounter_number} returned to Screening Review.",
            'data' => new EncounterSummaryResource($encounter),
        ]);
    }
}
