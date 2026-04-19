<?php

namespace App\Http\Controllers;

use App\Actions\Encounter\QueueEncounterToTriageAction;
use App\Actions\Encounter\SearchPatientAction;
use App\Actions\Encounter\StartEncounterAction;
use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Http\Requests\StartEncounterRequest;
use App\Models\Encounter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function __construct(
        private readonly StartEncounterAction          $startAction,
        private readonly SearchPatientAction           $searchAction,
        private readonly QueueEncounterToTriageAction  $queueToTriageAction,
    ) {}

    /**
     * GET /registration
     * Show the registration desk with today's active encounters.
     */
    public function index(): View
    {
        $activeEncounters = Encounter::with(['patient', 'registrationRecord'])
            ->where('current_stage', EncounterStage::Registration->value)
            ->whereIn('current_status', [
                EncounterStatus::Started->value,
                EncounterStatus::InProgress->value,
            ])
            ->latest('started_at')
            ->paginate(15);

        return view('registration.index', compact('activeEncounters'));
    }

    /**
     * GET /registration/search-patient
     * Live patient search — returns JSON for the search panel.
     */
    public function searchPatient(Request $request): JsonResponse
    {
        $request->validate([
            'q'             => ['required', 'string', 'min:2', 'max:100'],
            'date_of_birth' => ['nullable', 'date'],
        ]);

        $patients = $this->searchAction->handle(
            query:       $request->input('q'),
            dateOfBirth: $request->input('date_of_birth'),
        );

        return response()->json([
            'patients' => $patients->map(fn ($p) => [
                'id'           => $p->id,
                'patient_id'   => $p->patient_id,
                'full_name'    => $p->full_name,
                'gender'       => $p->gender,
                'date_of_birth'=> $p->date_of_birth?->format('Y-m-d'),
                'phone_number' => $p->phone_number,
                'nrc_number'   => $p->nrc_number,
            ]),
            'count' => $patients->count(),
        ]);
    }

    /**
     * POST /encounters/start
     * Start a new encounter for an existing or new patient.
     */
    public function start(StartEncounterRequest $request): RedirectResponse
    {
        $encounter = $this->startAction->handle(
            data:        $request->validated(),
            registrarId: auth()->id(),
        );

        return redirect()
            ->route('registration.encounter', $encounter)
            ->with('success', "Encounter {$encounter->encounter_number} started.");
    }

    /**
     * GET /registration/encounters/{encounter}
     * Show a single active encounter card with the queue-to-triage action.
     */
    public function showEncounter(Encounter $encounter): View
    {
        $encounter->load([
            'patient',
            'registrationRecord.registrar',
            'stageLogs',
            'queueTransitions',
            'audits',
        ]);

        return view('registration.encounter', compact('encounter'));
    }

    /**
     * POST /encounters/{encounter}/queue/triage
     * Complete registration and push encounter to the triage queue.
     */
    public function queueToTriage(Request $request, Encounter $encounter): RedirectResponse
    {
        $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->queueToTriageAction->handle(
            encounter: $encounter,
            queuedBy:  auth()->id(),
            notes:     $request->input('notes'),
        );

        return redirect()
            ->route('registration.index')
            ->with('success', "Encounter {$encounter->encounter_number} queued to Triage.");
    }
}
