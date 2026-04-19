<?php

namespace App\Http\Controllers;

use App\Actions\Encounter\CloseEncounterAction;
use App\Actions\Encounter\DispenseMedicationAction;
use App\Actions\Encounter\ReceivePharmacyQueueAction;
use App\Enums\EncounterStage;
use App\Enums\EncounterStatus;
use App\Http\Requests\CloseEncounterRequest;
use App\Http\Requests\DispenseMedicationRequest;
use App\Models\Encounter;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PharmacyController extends Controller
{
    // GET /pharmacy/queue
    public function queue(): View
    {
        $encounters = Encounter::with('patient')
            ->where('current_stage', EncounterStage::Pharmacy)
            ->where('current_status', EncounterStatus::Queued)
            ->orderBy('updated_at')
            ->get();

        return view('pharmacy.queue', compact('encounters'));
    }

    // POST /pharmacy/{encounter}/receive
    public function receive(
        Encounter $encounter,
        ReceivePharmacyQueueAction $action,
    ): RedirectResponse {
        $action->handle($encounter, auth()->id());
        return redirect()->route('pharmacy.show', $encounter);
    }

    // GET /pharmacy/{encounter}
    public function show(Encounter $encounter): View
    {
        $encounter->loadMissing([
            'patient',
            'prescription.items',
            'prescription.prescribedBy',
            'dispense.items',
            'screeningReviewRecord',
            'screeningRecord',
        ]);

        return view('pharmacy.show', compact('encounter'));
    }

    // POST /pharmacy/{encounter}/dispense
    public function dispense(
        DispenseMedicationRequest $request,
        Encounter $encounter,
        DispenseMedicationAction $action,
    ): RedirectResponse {
        $action->handle($encounter, $request->validated(), auth()->id());
        return redirect()->route('pharmacy.show', $encounter)->with('success', 'Medications dispensed.');
    }

    // POST /pharmacy/{encounter}/close
    public function close(
        CloseEncounterRequest $request,
        Encounter $encounter,
        CloseEncounterAction $action,
    ): RedirectResponse {
        $action->handle($encounter, auth()->id(), $request->validated()['closure_notes'] ?? null);
        return redirect()->route('pharmacy.show', $encounter)->with('success', 'Encounter closed and locked.');
    }
}
