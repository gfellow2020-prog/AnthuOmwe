<?php

namespace App\Http\Controllers;

use App\Actions\Encounter\QueueEncounterToScreeningAction;
use App\Actions\Encounter\ReceiveTriageQueueAction;
use App\Actions\Encounter\RecordTriageAction;
use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Http\Requests\TriageRequest;
use App\Models\Encounter;
use App\Models\Medication;
use App\Models\StartupMedication;
use App\Models\TriageRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\JsonResponse;
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
     * GET /triage/vitals
     * List all recorded triage vitals.
     */
    public function vitals(Request $request): View
    {
        $search = $request->input('search');

        $records = TriageRecord::with(['patient', 'encounter', 'nurse'])
            ->when($search, function ($query, $search) {
                $query->whereHas('patient', fn ($q) => $q->where('full_name', 'like', "%{$search}%"))
                      ->orWhereHas('encounter', fn ($q) => $q->where('encounter_number', 'like', "%{$search}%"));
            })
            ->orderByDesc('triage_at')
            ->paginate(20)
            ->withQueryString();

        return view('triage.vitals', compact('records', 'search'));
    }

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
            'startupMedications.recordedBy',
            'stageLogs',
            'queueTransitions',
            'audits.actionBy',
        ]);

        // Previous encounters for the same patient (excluding current)
        $pastEncounters = Encounter::where('patient_id', $encounter->patient_id)
            ->where('id', '!=', $encounter->id)
            ->with(['triageRecord', 'startupMedications.recordedBy'])
            ->orderByDesc('started_at')
            ->get();

        return view('triage.show', compact('encounter', 'pastEncounters'));
    }

    /**
     * POST /triage/{encounter}/save-vitals
     * Save (or update) triage vitals — does NOT advance stage.
     */
    public function saveVitals(TriageRequest $request, Encounter $encounter): RedirectResponse
    {
        if ($encounter->current_stage->value !== EncounterStage::Triage->value) {
            return redirect()
                ->route('triage.show', $encounter)
                ->with('error', 'This encounter is no longer at the triage stage.');
        }

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
        if ($encounter->current_stage->value !== EncounterStage::Triage->value) {
            return redirect()
                ->route('triage.show', $encounter)
                ->with('error', 'This encounter is no longer at the triage stage and cannot be completed.');
        }

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

    /**
     * GET /triage/startup-medications
     * List all startup medications across encounters.
     */
    public function startupMedications(Request $request): View
    {
        $search = $request->input('search');

        $medications = StartupMedication::with(['patient', 'encounter', 'triageRecord', 'recordedBy'])
            ->when($search, function ($query, $search) {
                $query->where('medication_name', 'like', "%{$search}%")
                      ->orWhereHas('patient', fn ($q) => $q->where('full_name', 'like', "%{$search}%"))
                      ->orWhereHas('encounter', fn ($q) => $q->where('encounter_number', 'like', "%{$search}%"));
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('triage.startup-medications', compact('medications', 'search'));
    }

    /**
     * POST /triage/{encounter}/startup-medications
     * Add a startup medication to an encounter.
     */
    public function storeStartupMedication(Request $request, Encounter $encounter): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'medication_id'   => ['nullable', 'exists:medications,id'],
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage'          => ['nullable', 'string', 'max:100'],
            'route'           => ['nullable', 'string', 'max:50'],
            'frequency'       => ['nullable', 'string', 'max:50'],
            'notes'           => ['nullable', 'string', 'max:1000'],
            'administered_at' => ['nullable', 'date'],
        ]);

        $med = $encounter->startupMedications()->create(array_merge($validated, [
            'triage_record_id' => $encounter->triageRecord?->id,
            'patient_id'       => $encounter->patient_id,
            'recorded_by'      => auth()->id(),
        ]));

        if ($request->expectsJson()) {
            $med->load('recordedBy');
            return response()->json([
                'success' => true,
                'med'     => [
                    'id'              => $med->id,
                    'medication_name' => $med->medication_name,
                    'dosage'          => $med->dosage,
                    'route'           => $med->route,
                    'frequency'       => $med->frequency,
                    'notes'           => $med->notes,
                    'administered_at' => $med->administered_at?->format('d M Y H:i'),
                    'recorded_by'     => $med->recordedBy?->name,
                    'destroy_url'     => route('triage.startup-medications.destroy', $med),
                ],
                'count'   => $encounter->startupMedications()->count(),
            ]);
        }

        return redirect()
            ->route('triage.show', $encounter)
            ->with('success', 'Startup medication added.');
    }

    /**
     * GET /medications/search?q=
     * JSON autocomplete endpoint.
     */
    public function searchMedications(Request $request): JsonResponse
    {
        $q = $request->query('q', '');

        $results = Medication::active()
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', "%{$q}%")
                      ->orWhere('generic_name', 'like', "%{$q}%");
            })
            ->orderBy('name')
            ->limit(20)
            ->get(['id', 'name', 'generic_name', 'strength', 'form', 'category', 'default_route', 'default_frequency']);

        return response()->json($results);
    }

    /**
     * DELETE /triage/startup-medications/{medication}
     * Remove a startup medication.
     */
    public function destroyStartupMedication(Request $request, StartupMedication $medication): RedirectResponse|JsonResponse
    {
        $encounterId = $medication->encounter_id;
        $medication->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'count'   => StartupMedication::where('encounter_id', $encounterId)->count(),
            ]);
        }

        return redirect()
            ->route('triage.show', $encounterId)
            ->with('success', 'Startup medication removed.');
    }
}
