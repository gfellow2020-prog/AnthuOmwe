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
use Illuminate\Support\Facades\DB;
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

        $selectedHouseholdOption = null;

        if (old('household_id')) {
            $selectedHouseholdOption = DB::table('households')
                ->select('household_id', 'head_of_house')
                ->where('household_id', old('household_id'))
                ->first();
        }

        $villages = DB::table('villages')
            ->select('name')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        return view('registration.index', compact('activeEncounters', 'selectedHouseholdOption', 'villages'));
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
            'sex'           => ['nullable', 'string', 'in:male,female'],
        ]);

        $patients = $this->searchAction->handle(
            query:       $request->input('q'),
            dateOfBirth: $request->input('date_of_birth'),
            sex:         $request->input('sex'),
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
     * GET /registration/search-households
     * Live household search for the registration form.
     */
    public function searchHouseholds(Request $request): JsonResponse
    {
        $request->validate([
            'q' => ['required', 'string', 'min:1', 'max:100'],
        ]);

        $query = trim((string) $request->input('q'));
        $like = '%' . $query . '%';

        $households = DB::table('households')
            ->select('household_id', 'head_of_house')
            ->where(function ($builder) use ($like) {
                $builder->where('head_of_house', 'like', $like)
                    ->orWhere('household_id', 'like', $like);
            })
            ->orderBy('head_of_house')
            ->orderBy('household_id')
            ->limit(3)
            ->get();

        return response()->json([
            'households' => $households->map(fn ($household) => [
                'id' => $household->household_id,
                'name' => $household->head_of_house ?: 'Unnamed Household',
                'label' => ($household->head_of_house ?: 'Unnamed Household') . ' (' . $household->household_id . ')',
            ]),
            'count' => $households->count(),
        ]);
    }

    /**
     * POST /registration/villages
     * Add a new village to the villages table and return it for use in the form.
     */
    public function addVillage(Request $request): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:villages,name'],
        ], [
            'name.required' => 'Village name is required.',
            'name.unique'   => 'That village already exists in the list.',
            'name.max'      => 'Village name must not exceed 100 characters.',
        ]);

        $name = trim((string) $request->input('name'));

        DB::table('villages')->insert([
            'name'       => $name,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['name' => $name], 201);
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
