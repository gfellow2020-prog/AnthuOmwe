<?php

namespace App\Http\Controllers;

use App\Actions\Encounter\CreatePrescriptionAction;
use App\Actions\Encounter\QueueEncounterToPharmacyAction;
use App\Actions\Encounter\ReceiveScreeningReviewQueueAction;
use App\Actions\Encounter\RecordScreeningReviewAction;
use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Http\Requests\ScreeningReviewRequest;
use App\Models\Encounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ScreeningReviewController extends Controller
{
    public function __construct(
        private readonly ReceiveScreeningReviewQueueAction $receiveAction,
        private readonly RecordScreeningReviewAction       $reviewAction,
        private readonly CreatePrescriptionAction          $prescriptionAction,
        private readonly QueueEncounterToPharmacyAction    $pharmacyAction,
    ) {}

    /**
     * GET /screening-review/queue
     */
    public function queue(): View
    {
        $queued = Encounter::with(['patient'])
            ->where('current_stage', EncounterStage::ScreeningReview->value)
            ->where('current_status', EncounterStatus::Queued->value)
            ->orderBy('updated_at')
            ->paginate(20, pageName: 'queued_page');

        $inProgress = Encounter::with(['patient', 'prescription'])
            ->where('current_stage', EncounterStage::ScreeningReview->value)
            ->where('current_status', EncounterStatus::InProgress->value)
            ->orderBy('updated_at')
            ->paginate(20, pageName: 'progress_page');

        return view('screening-review.queue', compact('queued', 'inProgress'));
    }

    /**
     * POST /screening-review/{encounter}/receive
     */
    public function receive(Encounter $encounter): RedirectResponse
    {
        $this->receiveAction->handle($encounter, auth()->id());

        return redirect()
            ->route('screening-review.show', $encounter)
            ->with('success', "Patient {$encounter->patient->full_name} received into Screening Review.");
    }

    /**
     * GET /screening-review/{encounter}
     */
    public function show(Encounter $encounter): View
    {
        $encounter->load([
            'patient',
            'triageRecord',
            'screeningRecord',
            'screeningReviewRecord',
            'labRequest.items',
            'labRequest.results',
            'prescription.items',
            'audits.actionBy',
        ]);

        return view('screening-review.show', compact('encounter'));
    }

    /**
     * POST /screening-review/{encounter}/complete
     * Saves the review data, creates the prescription, and queues to pharmacy — all in one go.
     */
    public function complete(ScreeningReviewRequest $request, Encounter $encounter): RedirectResponse
    {
        $data = $request->validated();

        // 1. Save the post-lab review screening record
        $reviewRecord = $this->reviewAction->handle($encounter, [
            'final_diagnosis'       => $data['final_diagnosis'],
            'clinical_findings'     => $data['clinical_findings']    ?? null,
            'physical_examination'  => $data['physical_examination'] ?? null,
            'assessment_notes'      => $data['assessment_notes']     ?? null,
            'plan'                  => $data['plan']                 ?? null,
            'review_notes'          => $data['review_notes']         ?? null,
        ], auth()->id());

        // 2. Create prescription
        $this->prescriptionAction->handle(
            encounter:       $encounter,
            data:            [
                'notes' => $data['prescription_notes'] ?? null,
                'items' => $data['items'],
            ],
            prescribedById:  auth()->id(),
            screeningRecord: $reviewRecord,
        );

        $encounter->refresh();

        // 3. Queue to pharmacy
        $this->pharmacyAction->handle($encounter, auth()->id());

        return redirect()
            ->route('screening-review.queue')
            ->with('success', "Encounter {$encounter->encounter_number} queued to Pharmacy.");
    }
}
