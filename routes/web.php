<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseholdsController;
use App\Http\Controllers\EncounterController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\PatientsController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\ScreeningReviewController;
use App\Http\Controllers\TriageController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest routes
Route::middleware('web')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

// Authenticated routes
Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/reception-dashboard', [AuthController::class, 'receptionDashboard'])->name('reception.dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Patients
    Route::get('/patients', [PatientsController::class, 'index'])->name('patients.index');
    Route::get('/patients/create', [PatientsController::class, 'create'])->name('patients.create');
    Route::post('/patients', [PatientsController::class, 'store'])->name('patients.store');
    Route::get('/patients/{ref}', [PatientsController::class, 'show'])->name('patients.show');

    // Households
    Route::get('/households', [HouseholdsController::class, 'index'])->name('households.index');
    Route::get('/households/{ref}', [HouseholdsController::class, 'show'])->name('households.show');

    // ── Registration (Phase 1) ───────────────────────────────────────────────
    Route::get('/registration', [RegistrationController::class, 'index'])->name('registration.index');
    Route::get('/registration/search-patient', [RegistrationController::class, 'searchPatient'])->name('registration.search');
    Route::get('/registration/encounters/{encounter}', [RegistrationController::class, 'showEncounter'])->name('registration.encounter');
    Route::post('/encounters/start', [RegistrationController::class, 'start'])->name('encounters.start');
    Route::post('/encounters/{encounter}/queue/triage', [RegistrationController::class, 'queueToTriage'])->name('encounters.queue.triage');

    // ── Triage (Phase 2) ─────────────────────────────────────────────────────
    Route::get('/triage/queue', [TriageController::class, 'queue'])->name('triage.queue');
    Route::post('/triage/{encounter}/receive', [TriageController::class, 'receive'])->name('triage.receive');
    Route::get('/triage/{encounter}', [TriageController::class, 'show'])->name('triage.show');
    Route::post('/triage/{encounter}/save-vitals', [TriageController::class, 'saveVitals'])->name('triage.save-vitals');
    Route::post('/triage/{encounter}/complete', [TriageController::class, 'complete'])->name('triage.complete');

    // ── Screening (Phase 3) ──────────────────────────────────────────────────
    Route::get('/screening/queue', [ScreeningController::class, 'queue'])->name('screening.queue');
    Route::post('/screening/{encounter}/receive', [ScreeningController::class, 'receive'])->name('screening.receive');
    Route::get('/screening/{encounter}', [ScreeningController::class, 'show'])->name('screening.show');
    Route::post('/screening/{encounter}/complete', [ScreeningController::class, 'complete'])->name('screening.complete');

    // ── Lab (Phase 4) ────────────────────────────────────────────────────────
    Route::get('/lab/queue', [LabController::class, 'queue'])->name('lab.queue');
    Route::post('/lab/{encounter}/receive', [LabController::class, 'receive'])->name('lab.receive');
    Route::get('/lab/{encounter}', [LabController::class, 'show'])->name('lab.show');
    Route::post('/lab/{encounter}/samples', [LabController::class, 'samples'])->name('lab.samples');
    Route::post('/lab/{encounter}/results', [LabController::class, 'results'])->name('lab.results');
    Route::post('/lab/{encounter}/complete', [LabController::class, 'complete'])->name('lab.complete');

    // ── Screening Review (Phase 5) ────────────────────────────────────────────
    Route::get('/screening-review/queue', [ScreeningReviewController::class, 'queue'])->name('screening-review.queue');
    Route::post('/screening-review/{encounter}/receive', [ScreeningReviewController::class, 'receive'])->name('screening-review.receive');
    Route::get('/screening-review/{encounter}', [ScreeningReviewController::class, 'show'])->name('screening-review.show');
    Route::post('/screening-review/{encounter}/complete', [ScreeningReviewController::class, 'complete'])->name('screening-review.complete');

    // ── Pharmacy (Phase 6) ────────────────────────────────────────────────────
    Route::get('/pharmacy/queue', [PharmacyController::class, 'queue'])->name('pharmacy.queue');
    Route::post('/pharmacy/{encounter}/receive', [PharmacyController::class, 'receive'])->name('pharmacy.receive');
    Route::get('/pharmacy/{encounter}', [PharmacyController::class, 'show'])->name('pharmacy.show');
    Route::post('/pharmacy/{encounter}/dispense', [PharmacyController::class, 'dispense'])->name('pharmacy.dispense');
    Route::post('/pharmacy/{encounter}/close', [PharmacyController::class, 'close'])->name('pharmacy.close');

    // ── Encounter Profile (Phase 7) ───────────────────────────────────────────
    Route::get('/encounters', [EncounterController::class, 'index'])->name('encounters.index');
    Route::get('/encounters/{encounter}', [EncounterController::class, 'show'])->name('encounters.show');
});
