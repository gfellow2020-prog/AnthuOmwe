@extends('layouts.dashboard')

@section('title', 'Add Medication — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('medications.index') }}" class="hover:text-neutral-700 transition">Medications</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Add Medication</span>
@endsection

@section('content')

<div class="max-w-4xl">
    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700">
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
            <h2 class="text-base font-semibold text-neutral-900 dark:text-white">New Medication</h2>
            <a href="{{ route('medications.index') }}" class="text-sm text-neutral-500 hover:text-neutral-700">← Back to list</a>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('medications.store') }}">
                @csrf
                @include('medications._form', ['medication' => null])

                <div class="mt-6 flex items-center gap-3">
                    <button type="submit" class="px-5 py-2 text-sm font-semibold rounded bg-neutral-900 hover:bg-neutral-800 text-white transition">
                        Save Medication
                    </button>
                    <a href="{{ route('medications.index') }}" class="px-5 py-2 text-sm font-medium rounded border border-neutral-300 text-neutral-700 hover:bg-neutral-50 transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
