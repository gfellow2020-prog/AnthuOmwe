@extends('layouts.dashboard')

@section('title', $medication->name . ' — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<a href="{{ route('medications.index') }}" class="hover:text-neutral-700 transition">Medications</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">{{ $medication->name }}</span>
@endsection

@section('content')

<div class="max-w-4xl">

    {{-- Success flash --}}
    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white dark:bg-neutral-800 rounded-xl shadow-sm border border-neutral-200 dark:border-neutral-700">
        <div class="px-6 py-4 border-b border-neutral-100 dark:border-neutral-700 flex items-center justify-between">
            <h2 class="text-base font-semibold text-neutral-900 dark:text-white">Medication Details</h2>
            <div class="flex items-center gap-2">
                <a href="{{ route('medications.edit', $medication) }}"
                   class="px-3 py-1.5 text-xs font-semibold rounded bg-neutral-900 hover:bg-neutral-800 text-white transition">Edit</a>
                <form method="POST" action="{{ route('medications.destroy', $medication) }}" onsubmit="return confirm('Delete this medication?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="px-3 py-1.5 text-xs font-semibold rounded bg-red-600 hover:bg-red-700 text-white transition">Delete</button>
                </form>
                <a href="{{ route('medications.index') }}"
                   class="px-3 py-1.5 text-xs font-medium rounded border border-neutral-300 text-neutral-600 hover:bg-neutral-50 transition">← Back</a>
            </div>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-2 md:grid-cols-3 gap-y-5 gap-x-8">
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">ID</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $medication->id }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Name</p>
                    <p class="text-sm font-medium text-neutral-900 dark:text-white">{{ $medication->name }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Generic Name</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $medication->generic_name ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Category</p>
                    <p class="text-sm">
                        <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300">
                            {{ $medication->category }}
                        </span>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Form</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $medication->form }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Strength</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $medication->strength ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Default Route</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $medication->default_route ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Default Frequency</p>
                    <p class="text-sm text-neutral-900 dark:text-white">{{ $medication->default_frequency ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Controlled</p>
                    <p class="text-sm">
                        @if($medication->is_controlled)
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700">Yes</span>
                        @else
                            <span class="text-neutral-500">No</span>
                        @endif
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Status</p>
                    <p class="text-sm">
                        @if($medication->is_active)
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-green-100 text-green-700">Active</span>
                        @else
                            <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-neutral-200 text-neutral-600">Inactive</span>
                        @endif
                    </p>
                </div>
                @if($medication->notes)
                <div class="col-span-2 md:col-span-3">
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Notes</p>
                    <p class="text-sm text-neutral-700 dark:text-neutral-300">{{ $medication->notes }}</p>
                </div>
                @endif
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Created</p>
                    <p class="text-sm text-neutral-500">{{ $medication->created_at->format('d M Y H:i') }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-1">Updated</p>
                    <p class="text-sm text-neutral-500">{{ $medication->updated_at->format('d M Y H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
