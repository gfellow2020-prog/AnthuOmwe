<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HouseholdsController;
use App\Http\Controllers\EncounterController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\MedicationController;
use App\Http\Controllers\PatientsController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\PharmacyController;
use App\Http\Controllers\PlatformComplaintController;
use App\Http\Controllers\ScreeningController;
use App\Http\Controllers\ScreeningReviewController;
use App\Http\Controllers\TriageController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\CalendarController;

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
    Route::get('/patients/{ref}/edit', [PatientsController::class, 'edit'])->name('patients.edit');
    Route::put('/patients/{ref}', [PatientsController::class, 'update'])->name('patients.update');
    Route::get('/patients/{ref}/encounters', [PatientsController::class, 'encounters'])->name('patients.encounters');

    // Households
    Route::get('/households', [HouseholdsController::class, 'index'])->name('households.index');
    Route::post('/households/{ref}/members', [HouseholdsController::class, 'storeMember'])->name('households.members.store');
    Route::put('/households/{ref}/members/{patientRef}', [HouseholdsController::class, 'updateMember'])->name('households.members.update');
    Route::delete('/households/{ref}/members/{patientRef}', [HouseholdsController::class, 'removeMember'])->name('households.members.remove');
    Route::post('/households/{ref}/members/{patientRef}/transfer', [HouseholdsController::class, 'transferMember'])->name('households.members.transfer');
    Route::get('/households/{ref}/transfer-households/search', [HouseholdsController::class, 'searchTransferHouseholds'])->name('households.transfer-households.search');
    Route::get('/households/{ref}', [HouseholdsController::class, 'show'])->name('households.show');

    // ── Registration (Phase 1) ───────────────────────────────────────────────
    Route::get('/registration', [RegistrationController::class, 'index'])->name('registration.index');
    Route::get('/registration/search-patient', [RegistrationController::class, 'searchPatient'])->name('registration.search');
    Route::get('/registration/search-households', [RegistrationController::class, 'searchHouseholds'])->name('registration.households.search');
    Route::post('/registration/villages', [RegistrationController::class, 'addVillage'])->name('registration.villages.store');
    Route::get('/registration/encounters/{encounter}', [RegistrationController::class, 'showEncounter'])->name('registration.encounter');
    Route::post('/encounters/start', [RegistrationController::class, 'start'])->name('encounters.start');
    Route::post('/encounters/{encounter}/queue/triage', [RegistrationController::class, 'queueToTriage'])->name('encounters.queue.triage');

    // ── Triage (Phase 2) ─────────────────────────────────────────────────────
    Route::get('/triage/queue', [TriageController::class, 'queue'])->name('triage.queue');
    Route::get('/triage/vitals', [TriageController::class, 'vitals'])->name('triage.vitals');
    Route::get('/triage/startup-medications', [TriageController::class, 'startupMedications'])->name('triage.startup-medications');
    Route::post('/triage/{encounter}/receive', [TriageController::class, 'receive'])->name('triage.receive');
    Route::get('/triage/{encounter}', [TriageController::class, 'show'])->name('triage.show');
    Route::post('/triage/{encounter}/save-vitals', [TriageController::class, 'saveVitals'])->name('triage.save-vitals');
    Route::post('/triage/{encounter}/complete', [TriageController::class, 'complete'])->name('triage.complete');
    Route::post('/triage/{encounter}/startup-medications', [TriageController::class, 'storeStartupMedication'])->name('triage.startup-medications.store');
    Route::delete('/triage/startup-medications/{medication}', [TriageController::class, 'destroyStartupMedication'])->name('triage.startup-medications.destroy');
    Route::get('/medications/search', [TriageController::class, 'searchMedications'])->name('medications.search');
    Route::get('/medications', [MedicationController::class, 'index'])->name('medications.index');
    Route::get('/medications/create', [MedicationController::class, 'create'])->name('medications.create');
    Route::post('/medications', [MedicationController::class, 'store'])->name('medications.store');
    Route::get('/medications/{medication}', [MedicationController::class, 'show'])->name('medications.show');
    Route::get('/medications/{medication}/edit', [MedicationController::class, 'edit'])->name('medications.edit');
    Route::put('/medications/{medication}', [MedicationController::class, 'update'])->name('medications.update');
    Route::delete('/medications/{medication}', [MedicationController::class, 'destroy'])->name('medications.destroy');

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

    // Complaints
    Route::get('/complaints', [PlatformComplaintController::class, 'index'])->name('complaints.index');
    Route::post('/complaints', [PlatformComplaintController::class, 'store'])->name('complaints.store');

    // ── Notifications ──────────────────────────────────────────────────────────
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // ── Calendar & Events ──────────────────────────────────────────────────────
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar.index');
    Route::get('/calendar/events', [CalendarController::class, 'events'])->name('calendar.events');
    Route::get('/calendar/events/{event}', [CalendarController::class, 'show'])->name('calendar.events.show');
    Route::post('/calendar/events', [CalendarController::class, 'store'])->name('calendar.events.store');
    Route::put('/calendar/events/{event}', [CalendarController::class, 'update'])->name('calendar.events.update');
    Route::delete('/calendar/events/{event}', [CalendarController::class, 'destroy'])->name('calendar.events.destroy');

    // ── Settings ──────────────────────────────────────────────────────────
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/users',              [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create',       [UserController::class, 'create'])->name('users.create');
        Route::post('/users',             [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{user}/edit',  [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{user}',       [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{user}',    [UserController::class, 'destroy'])->name('users.destroy');
    });
});
