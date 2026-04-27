@extends('layouts.dashboard')

@section('title', 'Edit Patient — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('patients.index') }}" class="hover:text-neutral-700 transition">Patients</a>
<span class="mx-2">/</span>
<a href="{{ route('patients.show', ['ref' => $patient['patient_id']]) }}" class="hover:text-neutral-700 transition">{{ $patient['full_name'] ?? 'Patient' }}</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Edit</span>
@endsection

@section('content')

@if(session('success'))
    <div class="mb-5 p-4 rounded bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 text-sm">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="mb-5 p-4 rounded bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-300 text-sm">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('patients.update', ['ref' => $patient['patient_id']]) }}">
    @csrf
    @method('PUT')

    {{-- Personal Information --}}
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
        <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Personal Information</h3>
        </div>
        <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Full Name <span class="text-red-500">*</span></label>
                <input type="text" name="full_name" value="{{ old('full_name', $patient['full_name'] ?? '') }}" required
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Date of Birth <span class="text-red-500">*</span></label>
                <input type="date" name="date_of_birth" value="{{ old('date_of_birth', $patient['date_of_birth'] ?? '') }}" required
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Sex <span class="text-red-500">*</span></label>
                <select name="gender" required
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                    <option value="Male" {{ old('gender', $patient['gender'] ?? '') === 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender', $patient['gender'] ?? '') === 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">NRC Number</label>
                <input type="text" name="nrc_number" value="{{ old('nrc_number', $patient['nrc_number'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Country</label>
                <select name="country"
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                    <option value="">--Select--</option>
                    @foreach(['ZM' => 'Zambia', 'MW' => 'Malawi', 'MZ' => 'Mozambique', 'TZ' => 'Tanzania', 'ZW' => 'Zimbabwe', 'CD' => 'DR Congo', 'Other' => 'Other'] as $code => $name)
                        <option value="{{ $code }}" {{ old('country', $patient['country'] ?? '') === $code ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Contact Information --}}
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
        <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Contact Information</h3>
        </div>
        <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Phone Number</label>
                <input type="text" name="phone_number" value="{{ old('phone_number', $patient['phone_number'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Other Cellphone</label>
                <input type="text" name="other_cellphone" value="{{ old('other_cellphone', $patient['other_cellphone'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Landline</label>
                <input type="text" name="landline" value="{{ old('landline', $patient['landline'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $patient['email'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">House Number</label>
                <input type="text" name="house_number" value="{{ old('house_number', $patient['house_number'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Road / Street</label>
                <input type="text" name="road_street" value="{{ old('road_street', $patient['road_street'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Area</label>
                <input type="text" name="area" value="{{ old('area', $patient['area'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">City / Town / Village</label>
                <input type="text" name="city_town_village" value="{{ old('city_town_village', $patient['city_town_village'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Landmarks & Directions</label>
                <textarea name="landmarks" rows="2"
                          class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">{{ old('landmarks', $patient['landmarks'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Marital & Birth Details --}}
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
        <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Marital & Birth Details</h3>
        </div>
        <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Marital Status</label>
                <select name="marital_status"
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                    <option value="">--Select--</option>
                    @foreach(['Single', 'Married', 'Divorced', 'Widowed', 'Separated'] as $status)
                        <option value="{{ $status }}" {{ old('marital_status', $patient['marital_status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Spouse First Name</label>
                <input type="text" name="spouse_first_name" value="{{ old('spouse_first_name', $patient['spouse_first_name'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Spouse Surname</label>
                <input type="text" name="spouse_surname" value="{{ old('spouse_surname', $patient['spouse_surname'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Home Language</label>
                <select name="home_language"
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                    <option value="">--Select--</option>
                    @foreach(['Bemba', 'Nyanja', 'Tonga', 'Lozi', 'Kaonde', 'Lunda', 'Luvale', 'English', 'Chewa', 'Other'] as $lang)
                        <option value="{{ $lang }}" {{ old('home_language', $patient['home_language'] ?? '') === $lang ? 'selected' : '' }}>{{ $lang }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Born in Zambia</label>
                <select name="born_in_zambia"
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                    <option value="Yes" {{ old('born_in_zambia', $patient['born_in_zambia'] ?? '') === 'Yes' ? 'selected' : '' }}>Yes</option>
                    <option value="No" {{ old('born_in_zambia', $patient['born_in_zambia'] ?? '') === 'No' ? 'selected' : '' }}>No</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Province of Birth</label>
                <input type="text" name="province_of_birth" value="{{ old('province_of_birth', $patient['province_of_birth'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">District of Birth</label>
                <input type="text" name="district_of_birth" value="{{ old('district_of_birth', $patient['district_of_birth'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Place of Birth</label>
                <input type="text" name="place_of_birth" value="{{ old('place_of_birth', $patient['place_of_birth'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Occupation</label>
                <input type="text" name="occupation" value="{{ old('occupation', $patient['occupation'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
        </div>
    </div>

    {{-- Biometrics --}}
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
        <div class="px-5 py-3.5 border-b border-neutral-200 dark:border-neutral-700">
            <h3 class="text-sm font-semibold text-neutral-800 dark:text-neutral-100">Biometrics</h3>
        </div>
        <div class="px-5 py-4 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4">
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">ART Number</label>
                <input type="text" name="art_number" value="{{ old('art_number', $patient['art_number'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">NUPN</label>
                <input type="text" name="nupn" value="{{ old('nupn', $patient['nupn'] ?? '') }}"
                       class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            </div>
            <div>
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Blood Group</label>
                <select name="blood_group"
                        class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
                    <option value="">--Select--</option>
                    @foreach(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $bg)
                        <option value="{{ $bg }}" {{ old('blood_group', $patient['blood_group'] ?? '') === $bg ? 'selected' : '' }}>{{ $bg }}</option>
                    @endforeach
                </select>
            </div>
            <div class="sm:col-span-2 lg:col-span-3">
                <label class="block text-xs font-semibold text-neutral-600 dark:text-neutral-400 mb-1">Allergies</label>
                <textarea name="allergies" rows="2"
                          class="w-full px-3 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">{{ old('allergies', $patient['allergies'] ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Submit --}}
    <div class="flex justify-end gap-3 mb-5">
        <a href="{{ route('patients.show', ['ref' => $patient['patient_id']]) }}"
           class="px-5 py-2.5 text-sm font-medium rounded border border-neutral-300 dark:border-neutral-600 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
            Cancel
        </a>
        <button type="submit"
                class="px-5 py-2.5 text-sm font-medium rounded bg-neutral-900 hover:bg-neutral-800 dark:bg-white dark:hover:bg-neutral-200 text-white dark:text-neutral-900 transition">
            Save Changes
        </button>
    </div>
</form>

@endsection
