<?php

namespace App\Http\Controllers;

use App\Actions\Encounter\QueueEncounterToScreeningAction;
use App\Actions\Encounter\ReceiveTriageQueueAction;
use App\Actions\Encounter\RecordTriageAction;
use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Http\Requests\TriageRequest;
use App\Models\Encounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TriageController extends Controller
{
    public function __construct(
        private readonly ReceiveTriageQueueAction      $receiveAction,
        private readonly RecordTriageAction            $recordAction,
        private readonly QueueEncounterToScreeningAction $queueToScreeningAction,
    ) {}

    /**
     * GET /triage/queue
     * Triage nurse's queue — all encounters currently at stage=triage.
     */
    public function queue(): View
    {
        $queued = Encounter::with(['patient', 'queueTransitions'])
            ->where('current_stage', EncounterStage::Triage->value)
            ->where('current_status', EncounterStatus::Queued->value)
            ->orderBy('started_at')
            ->paginate(20, pageName: 'queued_page');

        $inProgress = Encounter::with(['patient', 'triageRecord'])
            ->where('current_stage', EncounterStage::Triage->value)
            ->where('current_status', EncounterStatus::InProgress->value)
            ->orderBy('started_at')
            ->paginate(20, pageName: 'progress_page');

        return view('triage.queue', compact('queued', 'inProgress'));
    }

    /**
     * POST /triage/{encounter}/receive
     * Nurse receives the patient from the triage queue.
     */
    public function receive(Encounter $encounter): RedirectResponse
    {
        $this->receiveAction->handle($encounter, auth()->id());

        return redirect()
            ->route('triage.show', $encounter)
            ->with('success', "Patient {$encounter->patient->full_name} received into triage.");
    }

    /**
     * GET /triage/{encounter}
     * Show the triage form for an in-progress encounter.
     */
    public function show(Encounter $encounter): View
    {
        $encounter->load([
            'patient',
            'triageRecord',
            'stageLogs',
            'queueTransitions',
            'audits.actionBy',
        ]);

        return view('triage.show', compact('encounter'));
    }

    /**
     * POST /triage/{encounter}/save-vitals
     * Save (or update) triage vitals — does NOT advance stage.
     */
    public function saveVitals(TriageRequest $request, Encounter $encounter): RedirectResponse
    {
        $this->recordAction->handle(
            encounter: $encounter,
            data:      $request->validated(),
            nurseId:   auth()->id(),
        );

        return redirect()
            ->route('triage.show', $encounter)
            ->with('success', 'Vitals saved successfully.');
    }

    /**
     * POST /triage/{encounter}/complete
     * Save final vitals and queue encounter to Screening.
     */
    public function complete(TriageRequest $request, Encounter $encounter): RedirectResponse
    {
        // Save vitals first (upsert — idempotent)
        $this->recordAction->handle(
            encounter: $encounter,
            data:      $request->validated(),
            nurseId:   auth()->id(),
        );

        // Then advance to screening
        $this->queueToScreeningAction->handle(
            encounter: $encounter,
            nurseId:   auth()->id(),
            notes:     $request->input('notes'),
        );

        return redirect()
            ->route('triage.queue')
            ->with('success', "Encounter {$encounter->encounter_number} queued to Screening.");
    }
}
