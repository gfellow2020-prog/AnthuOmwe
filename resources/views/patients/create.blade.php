@extends('layouts.reception')

@section('title', 'Register New Patient — Anthu Omwe Health Center')

@push('styles')
<style>
    /* ── Wizard tabs ── */
    .wizard-tabs { display: flex; gap: 0; overflow-x: auto; border-bottom: 2px solid #e5e7eb; }
    .wizard-tab {
        position: relative;
        padding: 12px 22px;
        font-size: 14px;
        font-weight: 600;
        white-space: nowrap;
        color: #6b7280;
        cursor: pointer;
        transition: color .15s;
        border-bottom: 3px solid transparent;
        margin-bottom: -2px;
    }
    .wizard-tab:hover { color: #1e40af; }
    .wizard-tab.active { color: #1e40af; border-bottom-color: #2563eb; }
    .wizard-tab .chevron {
        display: inline-block;
        margin-left: 10px;
        font-size: 11px;
        color: #9ca3af;
    }
    /* ── Form field helpers ── */
    .field-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }
    .field-label .req { color: #dc2626; margin-left: 2px; }
    .field-input {
        width: 100%;
        padding: 10px 14px;
        font-size: 14px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        color: #111827;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }
    .field-input:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
    .field-input::placeholder { color: #9ca3af; }
    select.field-input { appearance: auto; }
    textarea.field-input { resize: vertical; min-height: 80px; }
    .field-check { display: flex; align-items: center; gap: 8px; font-size: 13px; color: #4b5563; margin-top: 6px; }
    .field-check input[type="checkbox"] { width: 16px; height: 16px; accent-color: #2563eb; }
    .step-panel { display: none; }
    .step-panel.active { display: block; }
</style>
@endpush

@section('content')

{{-- ============================================================
     WIZARD NAVIGATION TABS
     ============================================================ --}}
<div class="bg-white dark:bg-gray-800 rounded-t-2xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    <div class="wizard-tabs" id="wizard-tabs">
        <button type="button" class="wizard-tab active" data-step="1">Personal Information <span class="chevron">›</span></button>
        <button type="button" class="wizard-tab" data-step="2">Marital, Birth & Education Details <span class="chevron">›</span></button>
        <button type="button" class="wizard-tab" data-step="3">Biometrics</button>
    </div>
</div>

{{-- ============================================================
     FORM
     ============================================================ --}}
<form method="POST" action="{{ route('patients.store') }}" id="patient-form">
    @csrf

    {{-- ── STEP 1: Personal Information ── --}}
    <div class="step-panel active" data-step="1">
        <div class="bg-white dark:bg-gray-800 rounded-b-2xl shadow-sm border border-t-0 border-gray-200 dark:border-gray-700 p-6 md:p-8 mb-6">

            <h2 class="text-lg font-bold text-indigo-900 dark:text-indigo-300 mb-5">Personal Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                {{-- First Name --}}
                <div>
                    <label class="field-label">First Name <span class="req">*</span></label>
                    <input type="text" name="first_name" class="field-input" placeholder="Enter First Name" value="{{ old('first_name') }}" required>
                    @error('first_name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                {{-- Surname --}}
                <div>
                    <label class="field-label">Surname <span class="req">*</span></label>
                    <input type="text" name="surname" class="field-input" placeholder="Enter Surname" value="{{ old('surname') }}" required>
                    @error('surname') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                {{-- Date of Birth --}}
                <div>
                    <label class="field-label">Date of birth <span class="req">*</span></label>
                    <input type="date" name="date_of_birth" class="field-input" value="{{ old('date_of_birth') }}" required>
                    <label class="field-check">
                        <input type="checkbox" name="dob_estimated" value="1" {{ old('dob_estimated') ? 'checked' : '' }}>
                        Date of birth is estimated
                    </label>
                    @error('date_of_birth') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                {{-- Sex --}}
                <div>
                    <label class="field-label">Sex <span class="req">*</span></label>
                    <select name="gender" class="field-input" required>
                        <option value="">--Select--</option>
                        <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                {{-- NRC --}}
                <div>
                    <label class="field-label">NRC <span class="req">*</span></label>
                    <input type="text" name="nrc_number" class="field-input" placeholder="______/__/__" value="{{ old('nrc_number') }}">
                    <label class="field-check">
                        <input type="checkbox" name="no_nrc" value="1" {{ old('no_nrc') ? 'checked' : '' }}>
                        Client does not have NRC
                    </label>
                    @error('nrc_number') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                {{-- Country --}}
                <div>
                    <label class="field-label">Country <span class="req">*</span></label>
                    <select name="country" class="field-input" required>
                        <option value="">--Select--</option>
                        <option value="ZM" {{ old('country', 'ZM') === 'ZM' ? 'selected' : '' }}>Zambia</option>
                        <option value="MW" {{ old('country') === 'MW' ? 'selected' : '' }}>Malawi</option>
                        <option value="MZ" {{ old('country') === 'MZ' ? 'selected' : '' }}>Mozambique</option>
                        <option value="TZ" {{ old('country') === 'TZ' ? 'selected' : '' }}>Tanzania</option>
                        <option value="ZW" {{ old('country') === 'ZW' ? 'selected' : '' }}>Zimbabwe</option>
                        <option value="CD" {{ old('country') === 'CD' ? 'selected' : '' }}>DR Congo</option>
                        <option value="Other" {{ old('country') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                    @error('country') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                {{-- Registration Date --}}
                <div>
                    <label class="field-label">Registration Date <span class="req">*</span></label>
                    <input type="date" name="registration_date" class="field-input" value="{{ old('registration_date', now()->format('Y-m-d')) }}" required>
                </div>
            </div>

            {{-- ── Contact Information ── --}}
            <h2 class="text-lg font-bold text-amber-800 dark:text-amber-400 mt-10 mb-5">Contact Information</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                {{-- Primary phone --}}
                <div class="md:col-span-2">
                    <div class="grid grid-cols-[140px_1fr] md:grid-cols-[140px_1fr_140px_1fr] gap-4">
                        <div>
                            <label class="field-label">Code <span class="req">*</span></label>
                            <select name="phone_code" class="field-input">
                                <option value="+260" {{ old('phone_code', '+260') === '+260' ? 'selected' : '' }}>ZM (+260)</option>
                                <option value="+265" {{ old('phone_code') === '+265' ? 'selected' : '' }}>MW (+265)</option>
                                <option value="+255" {{ old('phone_code') === '+255' ? 'selected' : '' }}>TZ (+255)</option>
                                <option value="+263" {{ old('phone_code') === '+263' ? 'selected' : '' }}>ZW (+263)</option>
                                <option value="+258" {{ old('phone_code') === '+258' ? 'selected' : '' }}>MZ (+258)</option>
                                <option value="+243" {{ old('phone_code') === '+243' ? 'selected' : '' }}>CD (+243)</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Cellphone Number <span class="req">*</span></label>
                            <input type="tel" name="cellphone" class="field-input" placeholder="Cellphone Number" value="{{ old('cellphone') }}">
                            @error('cellphone') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="field-label">Code</label>
                            <select name="other_phone_code" class="field-input">
                                <option value="">Country</option>
                                <option value="+260">ZM (+260)</option>
                                <option value="+265">MW (+265)</option>
                                <option value="+255">TZ (+255)</option>
                                <option value="+263">ZW (+263)</option>
                                <option value="+258">MZ (+258)</option>
                                <option value="+243">CD (+243)</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Other Cellphone Number</label>
                            <input type="tel" name="other_cellphone" class="field-input" placeholder="Other Cellphone Number" value="{{ old('other_cellphone') }}">
                        </div>
                    </div>
                    <label class="field-check">
                        <input type="checkbox" name="no_cellphone" value="1" {{ old('no_cellphone') ? 'checked' : '' }}>
                        Client does not have cellphone number
                    </label>
                </div>

                {{-- Landline --}}
                <div class="md:col-span-1">
                    <div class="grid grid-cols-[140px_1fr] gap-4">
                        <div>
                            <label class="field-label">Code</label>
                            <select name="landline_code" class="field-input">
                                <option value="">Country</option>
                                <option value="+260">ZM (+260)</option>
                                <option value="+265">MW (+265)</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Landline Number</label>
                            <input type="tel" name="landline" class="field-input" placeholder="Landline Number" value="{{ old('landline') }}">
                        </div>
                    </div>
                </div>
                {{-- Email --}}
                <div>
                    <label class="field-label">Email</label>
                    <input type="email" name="email" class="field-input" placeholder="Enter Email" value="{{ old('email') }}">
                </div>
                {{-- House Number --}}
                <div>
                    <label class="field-label">House Number</label>
                    <input type="text" name="house_number" class="field-input" placeholder="Enter House Number" value="{{ old('house_number') }}">
                </div>
                {{-- Road/Street --}}
                <div>
                    <label class="field-label">Road/Street</label>
                    <input type="text" name="road_street" class="field-input" placeholder="Enter Road/Street" value="{{ old('road_street') }}">
                </div>
                {{-- Area --}}
                <div>
                    <label class="field-label">Area</label>
                    <input type="text" name="area" class="field-input" placeholder="Enter Area" value="{{ old('area') }}">
                </div>
                {{-- City/Town/Village --}}
                <div>
                    <label class="field-label">City/Town/Village</label>
                    <input type="text" name="city_town_village" class="field-input" placeholder="Enter City/Town/Village" value="{{ old('city_town_village') }}">
                </div>
                {{-- Landmarks & Directions --}}
                <div class="md:col-span-2">
                    <label class="field-label">Landmarks & Directions</label>
                    <textarea name="landmarks" class="field-input" placeholder="Enter Landmarks & Directions">{{ old('landmarks') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 2: Marital, Birth & Education Details ── --}}
    <div class="step-panel" data-step="2">
        <div class="bg-white dark:bg-gray-800 rounded-b-2xl shadow-sm border border-t-0 border-gray-200 dark:border-gray-700 p-6 md:p-8 mb-6">

            {{-- Marital Status & Spouse Details --}}
            <h2 class="text-lg font-bold text-indigo-900 dark:text-indigo-300 mb-5">Marital Status & Spouse Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 mb-10">
                <div class="md:col-span-2">
                    <label class="field-label">Marital Status</label>
                    <select name="marital_status" class="field-input">
                        <option value="">--Select--</option>
                        <option value="Single" {{ old('marital_status') === 'Single' ? 'selected' : '' }}>Single</option>
                        <option value="Married" {{ old('marital_status') === 'Married' ? 'selected' : '' }}>Married</option>
                        <option value="Divorced" {{ old('marital_status') === 'Divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="Widowed" {{ old('marital_status') === 'Widowed' ? 'selected' : '' }}>Widowed</option>
                        <option value="Separated" {{ old('marital_status') === 'Separated' ? 'selected' : '' }}>Separated</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Spouse First Name</label>
                    <input type="text" name="spouse_first_name" class="field-input" placeholder="Enter Spouse First Name" value="{{ old('spouse_first_name') }}">
                </div>
                <div>
                    <label class="field-label">Spouse Surname</label>
                    <input type="text" name="spouse_surname" class="field-input" placeholder="Enter Spouse Surname" value="{{ old('spouse_surname') }}">
                </div>
            </div>

            {{-- Place of Birth & Religious Denomination --}}
            <h2 class="text-lg font-bold text-amber-800 dark:text-amber-400 mb-5">Place of Birth & Religious Denomination</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5 mb-10">
                <div>
                    <label class="field-label">Home Language <span class="req">*</span></label>
                    <select name="home_language" class="field-input">
                        <option value="">--Select--</option>
                        <option value="Bemba" {{ old('home_language') === 'Bemba' ? 'selected' : '' }}>Bemba</option>
                        <option value="Nyanja" {{ old('home_language') === 'Nyanja' ? 'selected' : '' }}>Nyanja</option>
                        <option value="Tonga" {{ old('home_language') === 'Tonga' ? 'selected' : '' }}>Tonga</option>
                        <option value="Lozi" {{ old('home_language') === 'Lozi' ? 'selected' : '' }}>Lozi</option>
                        <option value="Kaonde" {{ old('home_language') === 'Kaonde' ? 'selected' : '' }}>Kaonde</option>
                        <option value="Lunda" {{ old('home_language') === 'Lunda' ? 'selected' : '' }}>Lunda</option>
                        <option value="Luvale" {{ old('home_language') === 'Luvale' ? 'selected' : '' }}>Luvale</option>
                        <option value="English" {{ old('home_language') === 'English' ? 'selected' : '' }}>English</option>
                        <option value="Chewa" {{ old('home_language') === 'Chewa' ? 'selected' : '' }}>Chewa</option>
                        <option value="Other" {{ old('home_language') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Is Client Born In Zambia <span class="req">*</span></label>
                    <select name="born_in_zambia" class="field-input">
                        <option value="Yes" {{ old('born_in_zambia', 'Yes') === 'Yes' ? 'selected' : '' }}>Yes</option>
                        <option value="No" {{ old('born_in_zambia') === 'No' ? 'selected' : '' }}>No</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Province of Birth <span class="req">*</span></label>
                    <select name="province_of_birth" class="field-input">
                        <option value="">--Select--</option>
                        <option value="Central" {{ old('province_of_birth') === 'Central' ? 'selected' : '' }}>Central</option>
                        <option value="Copperbelt" {{ old('province_of_birth') === 'Copperbelt' ? 'selected' : '' }}>Copperbelt</option>
                        <option value="Eastern" {{ old('province_of_birth') === 'Eastern' ? 'selected' : '' }}>Eastern</option>
                        <option value="Luapula" {{ old('province_of_birth') === 'Luapula' ? 'selected' : '' }}>Luapula</option>
                        <option value="Lusaka" {{ old('province_of_birth') === 'Lusaka' ? 'selected' : '' }}>Lusaka</option>
                        <option value="Muchinga" {{ old('province_of_birth') === 'Muchinga' ? 'selected' : '' }}>Muchinga</option>
                        <option value="Northern" {{ old('province_of_birth') === 'Northern' ? 'selected' : '' }}>Northern</option>
                        <option value="North-Western" {{ old('province_of_birth') === 'North-Western' ? 'selected' : '' }}>North-Western</option>
                        <option value="Southern" {{ old('province_of_birth') === 'Southern' ? 'selected' : '' }}>Southern</option>
                        <option value="Western" {{ old('province_of_birth') === 'Western' ? 'selected' : '' }}>Western</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">District of Birth <span class="req">*</span></label>
                    <select name="district_of_birth" class="field-input">
                        <option value="">--Select--</option>
                        <option value="Lusaka" {{ old('district_of_birth') === 'Lusaka' ? 'selected' : '' }}>Lusaka</option>
                        <option value="Ndola" {{ old('district_of_birth') === 'Ndola' ? 'selected' : '' }}>Ndola</option>
                        <option value="Kitwe" {{ old('district_of_birth') === 'Kitwe' ? 'selected' : '' }}>Kitwe</option>
                        <option value="Chipata" {{ old('district_of_birth') === 'Chipata' ? 'selected' : '' }}>Chipata</option>
                        <option value="Livingstone" {{ old('district_of_birth') === 'Livingstone' ? 'selected' : '' }}>Livingstone</option>
                        <option value="Kabwe" {{ old('district_of_birth') === 'Kabwe' ? 'selected' : '' }}>Kabwe</option>
                        <option value="Mansa" {{ old('district_of_birth') === 'Mansa' ? 'selected' : '' }}>Mansa</option>
                        <option value="Mongu" {{ old('district_of_birth') === 'Mongu' ? 'selected' : '' }}>Mongu</option>
                        <option value="Solwezi" {{ old('district_of_birth') === 'Solwezi' ? 'selected' : '' }}>Solwezi</option>
                        <option value="Kasama" {{ old('district_of_birth') === 'Kasama' ? 'selected' : '' }}>Kasama</option>
                        <option value="Other" {{ old('district_of_birth') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Place of Birth</label>
                    <input type="text" name="place_of_birth" class="field-input" placeholder="Enter Place of Birth" value="{{ old('place_of_birth') }}">
                </div>
            </div>

            {{-- Education & Employment --}}
            <h2 class="text-lg font-bold text-amber-800 dark:text-amber-400 mb-5">Education & Employment</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <div>
                    <label class="field-label">Occupation</label>
                    <select name="occupation" class="field-input">
                        <option value="">--Select--</option>
                        <option value="Unemployed" {{ old('occupation') === 'Unemployed' ? 'selected' : '' }}>Unemployed</option>
                        <option value="Student" {{ old('occupation') === 'Student' ? 'selected' : '' }}>Student</option>
                        <option value="Farmer" {{ old('occupation') === 'Farmer' ? 'selected' : '' }}>Farmer</option>
                        <option value="Teacher" {{ old('occupation') === 'Teacher' ? 'selected' : '' }}>Teacher</option>
                        <option value="Health Worker" {{ old('occupation') === 'Health Worker' ? 'selected' : '' }}>Health Worker</option>
                        <option value="Civil Servant" {{ old('occupation') === 'Civil Servant' ? 'selected' : '' }}>Civil Servant</option>
                        <option value="Business" {{ old('occupation') === 'Business' ? 'selected' : '' }}>Business</option>
                        <option value="Mining" {{ old('occupation') === 'Mining' ? 'selected' : '' }}>Mining</option>
                        <option value="Retired" {{ old('occupation') === 'Retired' ? 'selected' : '' }}>Retired</option>
                        <option value="Other" {{ old('occupation') === 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ── STEP 3: Biometrics ── --}}
    <div class="step-panel" data-step="3">
        <div class="bg-white dark:bg-gray-800 rounded-b-2xl shadow-sm border border-t-0 border-gray-200 dark:border-gray-700 p-6 md:p-8 mb-6">

            <h2 class="text-lg font-bold text-indigo-900 dark:text-indigo-300 mb-5">Biometrics</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
                <div>
                    <label class="field-label">ART Number</label>
                    <input type="text" name="art_number" class="field-input" placeholder="Enter ART Number" value="{{ old('art_number') }}">
                </div>
                <div>
                    <label class="field-label">NUPN</label>
                    <input type="text" name="nupn" class="field-input" placeholder="Enter NUPN" value="{{ old('nupn') }}">
                </div>
                <div>
                    <label class="field-label">Blood Group</label>
                    <select name="blood_group" class="field-input">
                        <option value="">--Select--</option>
                        <option value="A+" {{ old('blood_group') === 'A+' ? 'selected' : '' }}>A+</option>
                        <option value="A-" {{ old('blood_group') === 'A-' ? 'selected' : '' }}>A-</option>
                        <option value="B+" {{ old('blood_group') === 'B+' ? 'selected' : '' }}>B+</option>
                        <option value="B-" {{ old('blood_group') === 'B-' ? 'selected' : '' }}>B-</option>
                        <option value="AB+" {{ old('blood_group') === 'AB+' ? 'selected' : '' }}>AB+</option>
                        <option value="AB-" {{ old('blood_group') === 'AB-' ? 'selected' : '' }}>AB-</option>
                        <option value="O+" {{ old('blood_group') === 'O+' ? 'selected' : '' }}>O+</option>
                        <option value="O-" {{ old('blood_group') === 'O-' ? 'selected' : '' }}>O-</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Allergies</label>
                    <textarea name="allergies" class="field-input" placeholder="Enter known allergies">{{ old('allergies') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Footer Buttons ── --}}
    <div class="flex items-center justify-between mt-2 mb-8">
        <a href="{{ route('reception.dashboard') }}" class="px-6 py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold transition">
            Cancel
        </a>
        <div class="flex gap-3">
            <button type="button" id="btn-prev" class="hidden px-6 py-2.5 rounded-full bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-semibold transition">
                Previous
            </button>
            <button type="button" id="btn-next" class="px-8 py-2.5 rounded-full bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold transition shadow-sm">
                Next
            </button>
            <button type="submit" id="btn-submit" class="hidden px-8 py-2.5 rounded-full bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition shadow-sm">
                Register Patient
            </button>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.wizard-tab');
    const panels = document.querySelectorAll('.step-panel');
    const btnNext = document.getElementById('btn-next');
    const btnPrev = document.getElementById('btn-prev');
    const btnSubmit = document.getElementById('btn-submit');
    let current = 1;
    const total = tabs.length;

    function goToStep(step) {
        if (step < 1 || step > total) return;
        current = step;

        tabs.forEach(t => t.classList.toggle('active', parseInt(t.dataset.step) === current));
        panels.forEach(p => p.classList.toggle('active', parseInt(p.dataset.step) === current));

        btnPrev.classList.toggle('hidden', current === 1);
        btnNext.classList.toggle('hidden', current === total);
        btnSubmit.classList.toggle('hidden', current !== total);
    }

    tabs.forEach(t => t.addEventListener('click', () => goToStep(parseInt(t.dataset.step))));
    btnNext.addEventListener('click', () => goToStep(current + 1));
    btnPrev.addEventListener('click', () => goToStep(current - 1));
});
</script>
@endpush
