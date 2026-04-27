<?php

namespace App\Http\Controllers;

use App\Actions\Encounter\CreatePrescriptionAction;
use App\Actions\Encounter\QueueEncounterToLabAction;
use App\Actions\Encounter\QueueEncounterToPharmacyFromScreeningAction;
use App\Actions\Encounter\ReceiveScreeningQueueAction;
use App\Actions\Encounter\RecordInitialScreeningAction;
use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Http\Requests\ScreeningRequest;
use App\Models\Encounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;

class ScreeningController extends Controller
{
    public function __construct(
        private readonly ReceiveScreeningQueueAction               $receiveAction,
        private readonly RecordInitialScreeningAction              $recordAction,
        private readonly CreatePrescriptionAction                  $prescriptionAction,
        private readonly QueueEncounterToLabAction                 $queueToLabAction,
        private readonly QueueEncounterToPharmacyFromScreeningAction $queueToPharmacyAction,
    ) {}

    /**
     * GET /screening/queue
     * Clinician's screening queue — queued and in-progress.
     */
    public function queue(): View
    {
        $queued = Encounter::with(['patient'])
            ->where('current_stage', EncounterStage::Screening->value)
            ->where('current_status', EncounterStatus::Queued->value)
            ->orderBy('updated_at')
            ->paginate(20, pageName: 'queued_page');

        $inProgress = Encounter::with(['patient', 'screeningRecord'])
            ->where('current_stage', EncounterStage::Screening->value)
            ->where('current_status', EncounterStatus::InProgress->value)
            ->orderBy('updated_at')
            ->paginate(20, pageName: 'progress_page');

        return view('screening.queue', compact('queued', 'inProgress'));
    }

    /**
     * POST /screening/{encounter}/receive
     * Clinician receives the patient into screening.
     */
    public function receive(Encounter $encounter): RedirectResponse
    {
        $this->receiveAction->handle($encounter, auth()->id());

        return redirect()
            ->route('screening.show', $encounter)
            ->with('success', "Patient {$encounter->patient->full_name} received into Screening.");
    }

    /**
     * GET /screening/{encounter}
     * Show the clinical assessment form.
     */
    public function show(Encounter $encounter): View
    {
        $encounter->load([
            'patient',
            'triageRecord',
            'startupMedications',
            'screeningRecord.staffAssignments.user',
            'prescription.items',
            'prescription.prescribedBy',
            'stageLogs',
            'audits.actionBy',
        ]);

        // Previous encounters for the same patient (excluding current)
        $pastEncounters = Encounter::where('patient_id', $encounter->patient_id)
            ->where('id', '!=', $encounter->id)
            ->with(['triageRecord', 'screeningRecord', 'startupMedications'])
            ->orderByDesc('started_at')
            ->get();

        $icd11Path = resource_path('data/icd11-library.txt');
        $icd11Library = [];

        if (File::exists($icd11Path)) {
            $icd11Library = collect(File::lines($icd11Path))
                ->map(fn (string $line) => trim(ltrim($line, "\xEF\xBB\xBF")))
                ->filter(fn (string $line) => $line !== '')
                ->values()
                ->all();
        }

        return view('screening.show', compact('encounter', 'pastEncounters', 'icd11Library'));
    }

    /**
     * POST /screening/{encounter}/complete
     * Save assessment and queue to Lab (when lab requested) or Pharmacy (direct).
     */
    public function complete(ScreeningRequest $request, Encounter $encounter): RedirectResponse
    {
        $data = $request->validated();

        // Always save / refresh the screening record first
        $screeningRecord = $this->recordAction->handle(
            encounter:   $encounter,
            data:        $data,
            clinicianId: auth()->id(),
        );

        // Save prescription items if submitted
        $prescriptionItems = [];
        if (!empty($data['prescriptions'])) {
            $decoded = json_decode($data['prescriptions'], true);
            if (is_array($decoded) && count($decoded) > 0) {
                $prescriptionItems = $decoded;
            }
        }
        if (count($prescriptionItems) > 0) {
            $this->prescriptionAction->handle(
                encounter:       $encounter,
                data:            ['notes' => null, 'items' => $prescriptionItems],
                prescribedById:  auth()->id(),
                screeningRecord: $screeningRecord,
            );
        }

        $encounter->refresh();
        $labRequested = (bool) ($data['lab_requested'] ?? false);

        if ($labRequested) {
            $this->queueToLabAction->handle(
                encounter:   $encounter,
                clinicianId: auth()->id(),
                notes:       $data['notes'] ?? null,
            );

            return redirect()
                ->route('screening.queue')
                ->with('success', "Encounter {$encounter->encounter_number} queued to Lab.");
        }

        $this->queueToPharmacyAction->handle(
            encounter:   $encounter,
            clinicianId: auth()->id(),
            notes:       $data['notes'] ?? null,
        );

        return redirect()
            ->route('screening.queue')
            ->with('success', "Encounter {$encounter->encounter_number} queued directly to Pharmacy.");
    }
}
