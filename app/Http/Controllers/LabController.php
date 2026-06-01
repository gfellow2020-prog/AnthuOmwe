<?php

namespace App\Http\Controllers;

use App\Actions\Encounter\QueueEncounterBackToScreeningAction;
use App\Actions\Encounter\ReceiveLabQueueAction;
use App\Actions\Encounter\RecordLabResultsAction;
use App\Actions\Encounter\RecordLabSamplesAction;
use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Http\Requests\LabRequestStoreRequest;
use App\Http\Requests\LabResultStoreRequest;
use App\Models\Encounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LabController extends Controller
{
    public function __construct(
        private readonly ReceiveLabQueueAction              $receiveAction,
        private readonly RecordLabSamplesAction             $samplesAction,
        private readonly RecordLabResultsAction             $resultsAction,
        private readonly QueueEncounterBackToScreeningAction $completeAction,
    ) {}

    /**
     * GET /lab/queue
     */
    public function queue(): View
    {
        $queued = Encounter::with(['patient'])
            ->where('current_stage', EncounterStage::Lab->value)
            ->where('current_status', EncounterStatus::Queued->value)
            ->orderBy('updated_at')
            ->paginate(20, pageName: 'queued_page');

        $inProgress = Encounter::with(['patient', 'labRequest'])
            ->where('current_stage', EncounterStage::Lab->value)
            ->where('current_status', EncounterStatus::InProgress->value)
            ->orderBy('updated_at')
            ->paginate(20, pageName: 'progress_page');

        return view('lab.queue', compact('queued', 'inProgress'));
    }

    /**
     * POST /lab/{encounter}/receive
     */
    public function receive(Encounter $encounter): RedirectResponse
    {
        $this->receiveAction->handle($encounter, auth()->id());

        return redirect()
            ->route('lab.show', $encounter)
            ->with('success', "Patient {$encounter->patient->full_name} received into Lab.");
    }

    /**
     * GET /lab/{encounter}
     */
    public function show(Encounter $encounter): View
    {
        $encounter->load([
            'patient',
            'triageRecord',
            'screeningRecord',
            'labRequest.items',
            'labRequest.samples',
            'labRequest.results.recordedBy',
            'audits.actionBy',
        ]);

        return view('lab.show', compact('encounter'));
    }

    /**
     * POST /lab/{encounter}/samples
     * Record sample collection.
     */
    public function samples(LabRequestStoreRequest $request, Encounter $encounter): RedirectResponse
    {
        $encounter->loadMissing('labRequest');

        if (! $encounter->labRequest) {
            return back()->with('error', 'No lab request found. Receive the patient first.');
        }

        $data = $request->validated();

        DB::transaction(function () use ($data, $encounter): void {
            if (! empty($data['items'])) {
                foreach ($data['items'] as $item) {
                    $encounter->labRequest->items()->create($item + ['status' => 'pending']);
                }
            }

            if (! empty($data['samples'])) {
                $this->samplesAction->handle(
                    $encounter->labRequest,
                    ['samples' => $data['samples']],
                    auth()->id(),
                );
            }
        });

        return redirect()
            ->route('lab.show', $encounter)
            ->with('success', 'Sample collection recorded.');
    }

    /**
     * POST /lab/{encounter}/results
     * Record test results.
     */
    public function results(LabResultStoreRequest $request, Encounter $encounter): RedirectResponse
    {
        $encounter->loadMissing('labRequest');

        if (! $encounter->labRequest) {
            return back()->with('error', 'No lab request found.');
        }

        $this->resultsAction->handle(
            $encounter->labRequest,
            ['results' => $request->validated()['results']],
            auth()->id(),
        );

        return redirect()
            ->route('lab.show', $encounter)
            ->with('success', 'Results recorded.');
    }

    /**
     * POST /lab/{encounter}/complete
     * Complete lab and queue back to Screening Review.
     */
    public function complete(LabResultStoreRequest $request, Encounter $encounter): RedirectResponse
    {
        $encounter->loadMissing('labRequest');

        if (! $encounter->labRequest) {
            return back()->with('error', 'No lab request found.');
        }

        DB::transaction(function () use ($encounter, $request): void {
            // Save the submitted results first
            $this->resultsAction->handle(
                $encounter->labRequest,
                ['results' => $request->validated()['results']],
                auth()->id(),
            );

            $encounter->refresh();

            $this->completeAction->handle(
                encounter:  $encounter,
                labTechId:  auth()->id(),
                notes:      $request->input('notes'),
            );
        });

        return redirect()
            ->route('lab.queue')
            ->with('success', "Encounter {$encounter->encounter_number} returned to Screening Review.");
    }
}
