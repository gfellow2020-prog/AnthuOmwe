<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EncounterController;
use App\Http\Controllers\Api\V1\LabController;
use App\Http\Controllers\Api\V1\MedicationController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\PatientController;
use App\Http\Controllers\Api\V1\PharmacyController;
use App\Http\Controllers\Api\V1\QueueController;
use App\Http\Controllers\Api\V1\RegistrationController;
use App\Http\Controllers\Api\V1\ScreeningController;
use App\Http\Controllers\Api\V1\ScreeningReviewController;
use App\Http\Controllers\Api\V1\TriageController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth.api')->group(function (): void {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        Route::get('/dashboard/summary', [DashboardController::class, 'summary']);

        Route::get('/patients', [PatientController::class, 'index']);
        Route::get('/patients/search', [PatientController::class, 'search']);
        Route::get('/patients/{ref}', [PatientController::class, 'show']);

        Route::get('/registration/search-patient', [RegistrationController::class, 'searchPatients']);
        Route::get('/registration/search-households', [RegistrationController::class, 'searchHouseholds']);
        Route::get('/villages', [RegistrationController::class, 'villages']);
        Route::post('/villages', [RegistrationController::class, 'storeVillage']);

        Route::get('/encounters', [EncounterController::class, 'index']);
        Route::post('/encounters', [EncounterController::class, 'store']);
        Route::get('/encounters/{encounter}', [EncounterController::class, 'show']);
        Route::post('/encounters/{encounter}/queue/triage', [EncounterController::class, 'queueToTriage']);

        Route::get('/queues/{stage}', [QueueController::class, 'index'])
            ->whereIn('stage', ['registration', 'triage', 'screening', 'lab', 'screening-review', 'pharmacy']);

        Route::post('/triage/{encounter}/receive', [TriageController::class, 'receive']);
        Route::post('/triage/{encounter}/vitals', [TriageController::class, 'saveVitals']);
        Route::post('/triage/{encounter}/complete', [TriageController::class, 'complete']);
        Route::post('/triage/{encounter}/startup-medications', [TriageController::class, 'storeStartupMedication']);
        Route::delete('/triage/startup-medications/{medication}', [TriageController::class, 'destroyStartupMedication']);

        Route::post('/screening/{encounter}/receive', [ScreeningController::class, 'receive']);
        Route::post('/screening/{encounter}/complete', [ScreeningController::class, 'complete']);

        Route::post('/lab/{encounter}/receive', [LabController::class, 'receive']);
        Route::post('/lab/{encounter}/samples', [LabController::class, 'samples']);
        Route::post('/lab/{encounter}/results', [LabController::class, 'results']);
        Route::post('/lab/{encounter}/complete', [LabController::class, 'complete']);

        Route::post('/screening-review/{encounter}/receive', [ScreeningReviewController::class, 'receive']);
        Route::post('/screening-review/{encounter}/complete', [ScreeningReviewController::class, 'complete']);

        Route::post('/pharmacy/{encounter}/receive', [PharmacyController::class, 'receive']);
        Route::post('/pharmacy/{encounter}/dispense', [PharmacyController::class, 'dispense']);
        Route::post('/pharmacy/{encounter}/close', [PharmacyController::class, 'close']);

        Route::get('/medications', [MedicationController::class, 'index']);
        Route::get('/medications/search', [MedicationController::class, 'index']);
        Route::get('/medications/{medication}', [MedicationController::class, 'show']);

        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
        Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead']);
    });
});
