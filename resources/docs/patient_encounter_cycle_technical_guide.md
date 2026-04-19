# Patient Encounter Cycle Implementation Guide (Laravel)
## Queue-Driven Technical Build Plan for Copilot

This guide is the **authoritative implementation reference** for building a full encounter-based patient workflow in an existing Laravel project.

The workflow must be implemented as a **strict queue pipeline** in this exact order:

1. Start Encounter / Registration
2. Triage
3. Screening (Initial Clinical Assessment)
4. Lab
5. Screening Review (Post-Lab Clinical Review)
6. Pharmacy
7. End / Close Encounter

## Core Technical Rules

- Build the workflow as a **state-controlled encounter engine**.
- Each stage must write to its **own dedicated table** and also update the shared encounter timeline.
- Every stage must be linked to:
  - `encounter_id`
  - `patient_id`
  - `staff user id`
  - timestamps
  - queue transition logs
- Every queue movement must be recorded in a dedicated transition table.
- No stage may be skipped.
- No stage may start unless the prior stage is completed correctly.
- Once the encounter is closed in Pharmacy, all encounter-linked records become **locked** and non-editable.
- Use **transactions** for all state transitions.
- Use **Form Requests** for validation.
- Use **service/action classes** for workflow logic.
- Use **feature tests** and **unit tests** at the end of each stage implementation.
- For easier testing, **do not implement roles and permissions yet**. Keep the workflow open to authenticated users or temporarily bypass authorization logic. However, still structure the code so permissions can be added later without refactoring the workflow engine.

---

# Phase 0 — Shared Foundation

This phase must be completed before any queue stage is implemented.

## 0.1 Encounter State Design

Create strict encounter states and stage constants.

### Encounter stages
- `registration`
- `triage`
- `screening`
- `lab`
- `screening_review`
- `pharmacy`
- `completed`

### Encounter statuses
- `started`
- `queued`
- `in_progress`
- `completed`
- `cancelled`

### Queue movement states
- `queued`
- `received`
- `completed`

Create these as:
- PHP enums if project supports them cleanly, or
- dedicated constant classes in `App\Support\Encounter`

Recommended files:
- `app/Enums/EncounterStage.php`
- `app/Enums/EncounterStatus.php`
- `app/Enums/QueueTransitionStatus.php`

## 0.2 Core Tables

Create migrations first for all foundational shared tables.

### Required migrations
1. `patients`
2. `encounters`
3. `encounter_stage_logs`
4. `encounter_queue_transitions`
5. `encounter_audits`

## 0.3 Required core table structure

### patients
Recommended fields:
- `id`
- `patient_number` unique
- `first_name`
- `middle_name` nullable
- `last_name`
- `gender`
- `date_of_birth`
- `phone` nullable
- `email` nullable
- `nrc` nullable
- `passport_number` nullable
- `address` nullable
- `next_of_kin_name` nullable
- `next_of_kin_phone` nullable
- `allergies_summary` nullable
- `chronic_conditions_summary` nullable
- `created_by` nullable
- `updated_by` nullable
- timestamps
- softDeletes

### encounters
Recommended fields:
- `id`
- `encounter_number` unique
- `patient_id`
- `current_stage`
- `current_status`
- `priority_level` nullable
- `visit_type` nullable
- `started_at`
- `closed_at` nullable
- `started_by` nullable
- `closed_by` nullable
- `closure_notes` nullable
- `is_locked` boolean default false
- timestamps
- softDeletes

### encounter_stage_logs
One record per encounter-stage lifecycle step.

Recommended fields:
- `id`
- `encounter_id`
- `patient_id`
- `stage_name`
- `stage_sequence`
- `status`
- `started_by` nullable
- `completed_by` nullable
- `started_at` nullable
- `completed_at` nullable
- `notes` nullable
- `metadata` json nullable
- timestamps

### encounter_queue_transitions
Track every queue movement.

Recommended fields:
- `id`
- `encounter_id`
- `patient_id`
- `from_stage` nullable
- `to_stage`
- `queued_by`
- `received_by` nullable
- `queued_at`
- `received_at` nullable
- `transition_notes` nullable
- `status`
- timestamps

### encounter_audits
Track major workflow actions.

Recommended fields:
- `id`
- `encounter_id`
- `patient_id`
- `action_name`
- `action_stage`
- `action_by`
- `old_values` json nullable
- `new_values` json nullable
- `notes` nullable
- `action_at`
- timestamps

## 0.4 Core Models and Relationships

Create:
- `Patient`
- `Encounter`
- `EncounterStageLog`
- `EncounterQueueTransition`
- `EncounterAudit`

Relationships:
- Patient `hasMany` Encounters
- Encounter `belongsTo` Patient
- Encounter `hasMany` EncounterStageLogs
- Encounter `hasMany` EncounterQueueTransitions
- Encounter `hasMany` EncounterAudits

## 0.5 Shared Workflow Service Layer

Create shared workflow infrastructure before stage-specific actions.

Recommended files:
- `app/Services/Encounter/EncounterWorkflowService.php`
- `app/Services/Encounter/EncounterAuditService.php`
- `app/Services/Encounter/EncounterQueueService.php`
- `app/Services/Encounter/EncounterLockService.php`

### Responsibilities

#### EncounterWorkflowService
- validate current stage
- validate current status
- move encounter to next stage
- mark stage start/completion
- enforce stage order
- prevent stage skipping

#### EncounterQueueService
- queue encounter to next department
- receive encounter in target department
- complete queue transition
- update encounter stage/status

#### EncounterAuditService
- write audit records for every stage action

#### EncounterLockService
- check if encounter is locked
- block update attempts on locked encounters
- provide reusable `assertNotLocked()` method

## 0.6 Shared Requests, Traits, and Helpers

Create reusable technical components:
- `App\Http\Requests\BaseEncounterRequest`
- `App\Traits\LocksEncounterRecords`
- `App\Support\Encounter\EncounterStageMap`
- `App\Support\Encounter\EncounterSequence`

## 0.7 Foundation Auto-Test Command

Run these at the end of Phase 0:

```bash
php artisan migrate:fresh
php artisan test --filter=Encounter
```

Also add a smoke feature test:
- encounter can be created
- encounter starts unlocked
- encounter has current stage = registration
- encounter logs can be written
- queue transitions can be written

---

# Phase 1 — Registration Queue Stage

This phase implements encounter creation and patient registration.

## Objective
Registrar starts an encounter, checks whether patient exists, creates patient if not found, creates registration record, and queues the encounter to triage.

## 1.1 Stage-specific tables

Create migration:
- `registration_records`

### registration_records fields
- `id`
- `encounter_id`
- `patient_id`
- `registrar_id`
- `was_existing_patient` boolean
- `search_reference` nullable
- `registration_notes` nullable
- `registered_at`
- timestamps

## 1.2 Registration Technical Rules

- Search for patient by:
  - patient number
  - NRC
  - phone
  - name + date of birth
- If patient exists:
  - reuse patient
  - create new encounter
  - create registration record
- If patient does not exist:
  - create patient first
  - then create encounter
  - then create registration record
- Registration completion must:
  - create stage log
  - create audit log
  - queue encounter to triage
  - update encounter current stage to `triage`
  - update encounter status to `queued`

## 1.3 Registration Classes

Create:
- `app/Models/RegistrationRecord.php`
- `app/Actions/Encounter/StartEncounterAction.php`
- `app/Actions/Encounter/RegisterOrAttachPatientAction.php`
- `app/Actions/Encounter/QueueEncounterToTriageAction.php`
- `app/Http/Requests/StartEncounterRequest.php`
- `app/Http/Controllers/RegistrationController.php`

## 1.4 Registration Routes

Recommended:
- `POST /encounters/start`
- `GET /registration/search-patient`
- `POST /encounters/{encounter}/queue/triage`

## 1.5 Registration UI Requirements

Build Registration Desk page with:
- patient search form
- create patient fallback form
- active registration card
- start encounter action
- queue to triage action
- registration status feedback

## 1.6 Registration Auto-Test Command

At the end of this phase, add feature tests for:
- existing patient can start new encounter
- new patient is created when not found
- registration record is saved
- encounter is queued to triage
- queue transition from registration to triage is saved
- audit log is written

Run:

```bash
php artisan test --filter=Registration
```

---

# Phase 2 — Triage Queue Stage

This phase implements nurse triage and queueing to screening.

## Objective
Triage receives patient from Registration, records vitals and startup interventions, then queues patient to Screening.

## 2.1 Stage-specific tables

Create migration:
- `triage_records`

### triage_records fields
- `id`
- `encounter_id`
- `patient_id`
- `nurse_id`
- `weight` nullable
- `height` nullable
- `bmi` nullable
- `temperature` nullable
- `pulse` nullable
- `respiratory_rate` nullable
- `systolic_bp` nullable
- `diastolic_bp` nullable
- `oxygen_saturation` nullable
- `blood_sugar` nullable
- `pain_scale` nullable
- `chief_complaint_brief` nullable
- `startup_interventions_notes` nullable
- `startup_medications_notes` nullable
- `triage_notes` nullable
- `triage_at`
- `completed_at` nullable
- timestamps

## 2.2 Triage Technical Rules

- Triage may only receive encounters whose current stage is `triage` and status is `queued`
- When triage receives patient:
  - mark queue transition as received
  - mark encounter status `in_progress`
  - create/open stage log for triage
- Nurse records:
  - vitals
  - startup medication or intervention
  - triage notes
- Completing triage must:
  - create triage record
  - mark triage stage as completed
  - create audit log
  - queue encounter to screening

## 2.3 Triage Classes

Create:
- `app/Models/TriageRecord.php`
- `app/Actions/Encounter/ReceiveTriageQueueAction.php`
- `app/Actions/Encounter/RecordTriageAction.php`
- `app/Actions/Encounter/QueueEncounterToScreeningAction.php`
- `app/Http/Requests/TriageRequest.php`
- `app/Http/Controllers/TriageController.php`

## 2.4 Triage Routes

Recommended:
- `GET /triage/queue`
- `POST /triage/{encounter}/receive`
- `POST /triage/{encounter}/complete`

## 2.5 Triage UI Requirements

Build Triage Queue page with:
- queued patients list
- receive encounter action
- triage form
- complete triage button
- queue to screening feedback

## 2.6 Triage Auto-Test Command

Feature tests:
- queued triage patient can be received
- vitals are saved correctly
- startup medication notes are saved
- encounter is queued to screening
- triage queue transition is completed
- stage log and audit log are written

Run:

```bash
php artisan test --filter=Triage
```

---

# Phase 3 — Screening Queue Stage (Initial Screening)

This phase implements first clinical assessment.

## Objective
Screening receives patient from Triage, captures clinical assessment, and either sends patient to Lab or directly to Pharmacy if no lab is needed.

## 3.1 Stage-specific tables

Create migrations:
- `screening_records`
- `screening_staff_assignments`

### screening_records fields
- `id`
- `encounter_id`
- `patient_id`
- `clinician_id`
- `screening_type` (`initial`, `review_after_lab`)
- `complaints` text nullable
- `history_of_presenting_illness` text nullable
- `past_medical_history` text nullable
- `medication_history` text nullable
- `allergy_history` text nullable
- `physical_examination` text nullable
- `clinical_findings` text nullable
- `provisional_diagnosis` text nullable
- `final_diagnosis` text nullable
- `assessment_notes` text nullable
- `plan` text nullable
- `lab_requested` boolean default false
- `referred_to_lab_at` nullable
- `returned_from_lab_at` nullable
- `review_notes` nullable
- `prescribed` boolean default false
- `screening_started_at` nullable
- `screening_completed_at` nullable
- timestamps

### screening_staff_assignments fields
- `id`
- `screening_record_id`
- `user_id`
- `role_name`
- `participation_type`
- `notes` nullable
- timestamps

## 3.2 Screening Technical Rules

- Screening may only receive encounters queued from triage
- On receive:
  - mark queue transition received
  - set encounter status `in_progress`
- Record:
  - complaints
  - history
  - assessment
  - physical findings
  - provisional diagnosis
  - plan
- If clinician requests lab:
  - create initial screening record
  - set `lab_requested = true`
  - queue encounter to lab
- If no lab required:
  - allow prescription flow
  - queue directly to pharmacy

## 3.3 Screening Classes

Create:
- `app/Models/ScreeningRecord.php`
- `app/Models/ScreeningStaffAssignment.php`
- `app/Actions/Encounter/ReceiveScreeningQueueAction.php`
- `app/Actions/Encounter/RecordInitialScreeningAction.php`
- `app/Actions/Encounter/QueueEncounterToLabAction.php`
- `app/Actions/Encounter/QueueEncounterToPharmacyAction.php`
- `app/Http/Requests/ScreeningRequest.php`
- `app/Http/Controllers/ScreeningController.php`

## 3.4 Screening Routes

Recommended:
- `GET /screening/queue`
- `POST /screening/{encounter}/receive`
- `POST /screening/{encounter}/complete`
- `POST /screening/{encounter}/queue/lab`
- `POST /screening/{encounter}/queue/pharmacy`

## 3.5 Screening UI Requirements

Build Screening Queue page with:
- queued encounters list
- receive button
- clinical assessment form
- request lab checkbox/workflow
- direct-to-pharmacy option when no lab needed
- screening staff assignment UI if multiple staff handled assessment

## 3.6 Screening Auto-Test Command

Feature tests:
- patient queued from triage can be received in screening
- initial screening data is saved
- encounter can be queued to lab
- encounter can be queued directly to pharmacy if lab not required
- screening staff assignments persist correctly
- stage and audit logs are written

Run:

```bash
php artisan test --filter=Screening
```

---

# Phase 4 — Lab Queue Stage

This phase implements lab request intake, sample collection, testing, and return to screening review.

## Objective
Lab receives patient from Screening, records samples and test findings, then queues patient back to Screening Review.

## 4.1 Stage-specific tables

Create migrations:
- `lab_requests`
- `lab_request_items`
- `lab_samples`
- `lab_results`

### lab_requests fields
- `id`
- `encounter_id`
- `patient_id`
- `screening_record_id`
- `requested_by`
- `request_number` unique
- `request_notes` nullable
- `priority_level` nullable
- `status`
- `requested_at`
- `completed_at` nullable
- timestamps

### lab_request_items fields
- `id`
- `lab_request_id`
- `test_code` nullable
- `test_name`
- `specimen_type` nullable
- `test_group` nullable
- `instructions` nullable
- `status`
- timestamps

### lab_samples fields
- `id`
- `lab_request_id`
- `encounter_id`
- `patient_id`
- `collected_by`
- `sample_type`
- `sample_label` nullable
- `collection_notes` nullable
- `collected_at`
- timestamps

### lab_results fields
- `id`
- `lab_request_id`
- `lab_request_item_id` nullable
- `encounter_id`
- `patient_id`
- `recorded_by`
- `verified_by` nullable
- `result_value` nullable
- `result_text` nullable
- `reference_range` nullable
- `interpretation` nullable
- `remarks` nullable
- `result_status`
- `result_recorded_at`
- `verified_at` nullable
- timestamps

## 4.2 Lab Technical Rules

- Lab only receives encounters queued from screening
- Lab request items should be generated from requested tests
- Sample collection and result recording should be separate actions
- Completing lab must:
  - ensure at least one result exists where required
  - update lab request status
  - queue encounter back to `screening_review`
  - log transition and audit

## 4.3 Lab Recommended Extension Tables

Do not build yet unless time allows, but keep structure extensible for:
- `lab_test_catalogs`
- `lab_test_groups`
- `specimen_types`
- `lab_reference_ranges`
- `lab_result_attachments`
- `lab_verifications`

## 4.4 Lab Classes

Create:
- `app/Models/LabRequest.php`
- `app/Models/LabRequestItem.php`
- `app/Models/LabSample.php`
- `app/Models/LabResult.php`
- `app/Actions/Encounter/ReceiveLabQueueAction.php`
- `app/Actions/Encounter/RecordLabSamplesAction.php`
- `app/Actions/Encounter/RecordLabResultsAction.php`
- `app/Actions/Encounter/QueueEncounterBackToScreeningAction.php`
- `app/Http/Requests/LabRequestStoreRequest.php`
- `app/Http/Requests/LabResultStoreRequest.php`
- `app/Http/Controllers/LabController.php`

## 4.5 Lab Routes

Recommended:
- `GET /lab/queue`
- `POST /lab/{encounter}/receive`
- `POST /lab/{encounter}/samples`
- `POST /lab/{encounter}/results`
- `POST /lab/{encounter}/complete`

## 4.6 Lab UI Requirements

Build Lab Queue page with:
- received/queued encounter list
- sample collection form
- test results form
- test item listing
- queue back to screening review action

## 4.7 Lab Auto-Test Command

Feature tests:
- lab can receive patient queued from screening
- lab request and items persist correctly
- samples persist correctly
- results persist correctly
- encounter is queued back to screening review
- lab stage and queue logs are correct

Run:

```bash
php artisan test --filter=Lab
```

---

# Phase 5 — Screening Review Stage (Post-Lab Review)

This phase implements return-to-clinician assessment after lab results.

## Objective
Screening Review receives the encounter from Lab, reviews findings, records final diagnosis and treatment plan, creates prescriptions, and queues patient to Pharmacy.

## 5.1 Screening Review Technical Rules

- Screening review can only receive encounters queued back from lab
- Screening review must:
  - load prior screening record(s)
  - load lab request(s) and result(s)
  - create a second screening record with `screening_type = review_after_lab`
- Record:
  - final clinical interpretation
  - final diagnosis
  - treatment plan
  - review notes
- Create prescription
- Queue encounter to pharmacy

## 5.2 Pharmacy Prescription Tables

Create migrations:
- `pharmacy_prescriptions`
- `pharmacy_prescription_items`

### pharmacy_prescriptions fields
- `id`
- `encounter_id`
- `patient_id`
- `screening_record_id`
- `prescribed_by`
- `prescription_number` unique
- `notes` nullable
- `status`
- `prescribed_at`
- timestamps

### pharmacy_prescription_items fields
- `id`
- `pharmacy_prescription_id`
- `drug_id` nullable
- `drug_name`
- `strength` nullable
- `formulation` nullable
- `dose`
- `frequency`
- `duration`
- `quantity_prescribed`
- `route` nullable
- `instructions` nullable
- timestamps

## 5.3 Screening Review Classes

Create:
- `app/Models/PharmacyPrescription.php`
- `app/Models/PharmacyPrescriptionItem.php`
- `app/Actions/Encounter/ReceiveScreeningReviewQueueAction.php`
- `app/Actions/Encounter/RecordScreeningReviewAction.php`
- `app/Actions/Encounter/CreatePrescriptionAction.php`
- `app/Actions/Encounter/QueueEncounterToPharmacyAction.php`
- `app/Http/Requests/ScreeningReviewRequest.php`
- `app/Http/Requests/PrescriptionRequest.php`
- `app/Http/Controllers/ScreeningReviewController.php`

## 5.4 Screening Review Routes

Recommended:
- `GET /screening-review/queue`
- `POST /screening-review/{encounter}/receive`
- `POST /screening-review/{encounter}/complete`
- `POST /screening-review/{encounter}/prescriptions`
- `POST /screening-review/{encounter}/queue/pharmacy`

## 5.5 Screening Review UI Requirements

Build Screening Review page with:
- encounter header
- initial screening summary
- lab findings summary
- final diagnosis form
- treatment plan form
- prescription entry form
- queue to pharmacy button

## 5.6 Screening Review Auto-Test Command

Feature tests:
- encounter queued from lab can be received in screening review
- post-lab screening review is saved
- prescription and items are saved
- encounter is queued to pharmacy
- audit and stage logs are saved correctly

Run:

```bash
php artisan test --filter=ScreeningReview
```

---

# Phase 6 — Pharmacy Queue Stage and Encounter Closure

This phase implements dispensing and hard encounter lock.

## Objective
Pharmacy receives patient from Screening Review, dispenses medication, then closes and locks the encounter.

## 6.1 Stage-specific tables

Create migrations:
- `pharmacy_dispenses`
- `pharmacy_dispense_items`

### pharmacy_dispenses fields
- `id`
- `encounter_id`
- `patient_id`
- `pharmacy_prescription_id`
- `dispensed_by`
- `dispensing_notes` nullable
- `counseling_notes` nullable
- `dispensed_at`
- timestamps

### pharmacy_dispense_items fields
- `id`
- `pharmacy_dispense_id`
- `pharmacy_prescription_item_id` nullable
- `drug_id` nullable
- `drug_name`
- `quantity_dispensed`
- `batch_no` nullable
- `stock_reference` nullable
- `instructions` nullable
- timestamps

## 6.2 Pharmacy Technical Rules

- Pharmacy only receives encounters queued from screening review or direct screening if lab was skipped
- Pharmacy must:
  - load active prescription
  - record dispensed items
  - record dispensing notes and counseling notes
- Close encounter must:
  - set `current_stage = completed`
  - set `current_status = completed`
  - set `closed_at`
  - set `closed_by`
  - set `is_locked = true`
  - mark final stage log complete
  - create final audit entry

## 6.3 Locking Requirements

After closure:
- no update allowed on:
  - registration records
  - triage records
  - screening records
  - lab requests
  - lab results
  - prescriptions
  - dispenses
  - encounter header
- enforce in:
  - service layer
  - Form Request prepare/validation
  - policies later if needed
  - model observers if useful

## 6.4 Pharmacy Classes

Create:
- `app/Models/PharmacyDispense.php`
- `app/Models/PharmacyDispenseItem.php`
- `app/Actions/Encounter/ReceivePharmacyQueueAction.php`
- `app/Actions/Encounter/DispenseMedicationAction.php`
- `app/Actions/Encounter/CloseEncounterAction.php`
- `app/Http/Requests/DispenseMedicationRequest.php`
- `app/Http/Requests/CloseEncounterRequest.php`
- `app/Http/Controllers/PharmacyController.php`

## 6.5 Pharmacy Routes

Recommended:
- `GET /pharmacy/queue`
- `POST /pharmacy/{encounter}/receive`
- `POST /pharmacy/{encounter}/dispense`
- `POST /pharmacy/{encounter}/close`

## 6.6 Pharmacy UI Requirements

Build Pharmacy Queue page with:
- queued encounters
- prescription summary
- dispense form
- dispense item list
- close encounter action
- locked/completed badge after closure

## 6.7 Pharmacy Auto-Test Command

Feature tests:
- pharmacy can receive queued encounter
- dispensed items are saved
- encounter closes successfully
- closed encounter becomes locked
- locked encounter cannot be edited
- final audit log exists

Run:

```bash
php artisan test --filter=Pharmacy
```

---

# Phase 7 — Unified Encounter Profile Page

This phase creates the full encounter timeline page for debugging, traceability, and future EMR usage.

## Objective
Build a single technical encounter profile page that renders the entire patient journey in sequence.

## 7.1 Encounter Profile Data Requirements

Display in order:
1. encounter header
2. patient demographics
3. registration data
4. triage data
5. initial screening data
6. lab request data
7. lab sample data
8. lab result data
9. screening review data
10. prescription data
11. dispensing data
12. queue transition timeline
13. encounter stage logs
14. audit logs
15. lock/completion state
16. all staff who handled the encounter across all stages

## 7.2 Technical Requirements

- eager load all related models
- group data by stage
- render stage sequence clearly
- support missing-lab path where lab was skipped
- support direct-to-pharmacy path
- show current encounter status
- show immutable/locked state badge
- minimize N+1 queries
- keep page usable for debugging tests

## 7.3 Encounter Profile Classes

Create:
- `app/Http/Controllers/EncounterController.php`
- a query/service for encounter full detail loading if needed:
  - `app/Services/Encounter/EncounterDetailService.php`

## 7.4 Encounter Profile Routes

Recommended:
- `GET /encounters`
- `GET /encounters/{encounter}`

## 7.5 Encounter Profile Auto-Test Command

Feature tests:
- full encounter profile loads successfully
- all related stage records appear in order
- all queue transitions are visible
- staff traceability is visible
- completed encounter shows locked state

Run:

```bash
php artisan test --filter=EncounterProfile
```

---

# Phase 8 — End-to-End Integration Tests

This phase proves that the entire queue pipeline works from start to finish.

## Objective
Create end-to-end tests for both major paths:
1. full path with lab
2. direct path without lab

## 8.1 Full path scenario
- create or attach patient
- start encounter
- queue to triage
- complete triage
- queue to screening
- request lab
- queue to lab
- record samples
- record results
- queue back to screening review
- create final diagnosis
- create prescription
- queue to pharmacy
- dispense medication
- close encounter
- verify locked state

## 8.2 Direct path scenario
- create or attach patient
- start encounter
- triage
- screening
- no lab requested
- create prescription
- queue to pharmacy
- dispense
- close encounter

## 8.3 Assertions
- every transition exists
- every stage log exists
- audits exist
- encounter state changes are correct
- locked encounter rejects changes

## 8.4 End-to-End Auto-Test Command

Run:

```bash
php artisan test --filter=EncounterFlow
php artisan test
```

---

# Technical Controller and Request Summary

## Controllers to create
- `EncounterController`
- `RegistrationController`
- `TriageController`
- `ScreeningController`
- `ScreeningReviewController`
- `LabController`
- `PharmacyController`

## Request classes to create
- `StartEncounterRequest`
- `TriageRequest`
- `ScreeningRequest`
- `LabRequestStoreRequest`
- `LabResultStoreRequest`
- `ScreeningReviewRequest`
- `PrescriptionRequest`
- `DispenseMedicationRequest`
- `CloseEncounterRequest`

---

# Technical Route Summary

Recommended workflow routes:

```php
Route::get('/encounters', [EncounterController::class, 'index']);
Route::get('/encounters/{encounter}', [EncounterController::class, 'show']);

Route::post('/encounters/start', [RegistrationController::class, 'start']);
Route::get('/registration/search-patient', [RegistrationController::class, 'searchPatient']);
Route::post('/encounters/{encounter}/queue/triage', [RegistrationController::class, 'queueToTriage']);

Route::get('/triage/queue', [TriageController::class, 'queue']);
Route::post('/triage/{encounter}/receive', [TriageController::class, 'receive']);
Route::post('/triage/{encounter}/complete', [TriageController::class, 'complete']);

Route::get('/screening/queue', [ScreeningController::class, 'queue']);
Route::post('/screening/{encounter}/receive', [ScreeningController::class, 'receive']);
Route::post('/screening/{encounter}/complete', [ScreeningController::class, 'complete']);
Route::post('/screening/{encounter}/queue/lab', [ScreeningController::class, 'queueToLab']);
Route::post('/screening/{encounter}/queue/pharmacy', [ScreeningController::class, 'queueToPharmacy']);

Route::get('/lab/queue', [LabController::class, 'queue']);
Route::post('/lab/{encounter}/receive', [LabController::class, 'receive']);
Route::post('/lab/{encounter}/samples', [LabController::class, 'storeSamples']);
Route::post('/lab/{encounter}/results', [LabController::class, 'storeResults']);
Route::post('/lab/{encounter}/complete', [LabController::class, 'complete']);

Route::get('/screening-review/queue', [ScreeningReviewController::class, 'queue']);
Route::post('/screening-review/{encounter}/receive', [ScreeningReviewController::class, 'receive']);
Route::post('/screening-review/{encounter}/complete', [ScreeningReviewController::class, 'complete']);
Route::post('/screening-review/{encounter}/prescriptions', [ScreeningReviewController::class, 'storePrescription']);
Route::post('/screening-review/{encounter}/queue/pharmacy', [ScreeningReviewController::class, 'queueToPharmacy']);

Route::get('/pharmacy/queue', [PharmacyController::class, 'queue']);
Route::post('/pharmacy/{encounter}/receive', [PharmacyController::class, 'receive']);
Route::post('/pharmacy/{encounter}/dispense', [PharmacyController::class, 'dispense']);
Route::post('/pharmacy/{encounter}/close', [PharmacyController::class, 'close']);
```

---

# Copilot Execution Instructions

Use this file as the strict build contract.

Implementation instructions for Copilot:
- execute phases in exact order
- do not jump ahead
- do not implement future stages before dependencies exist
- at the end of each phase, generate or update tests first, then run the listed test command
- only move to the next phase after current phase tests pass
- use transactional service/action classes for every workflow mutation
- keep all encounter state transitions centralized
- prefer maintainable, production-grade Laravel structure over shortcuts
- no pseudo code
- generate working code only
