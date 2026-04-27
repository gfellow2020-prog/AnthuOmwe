@extends('layouts.dashboard')

@section('title', 'Medications Catalog — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Medications</span>
@endsection

@section('content')

{{-- Success flash --}}
@if(session('success'))
<div class="mb-4 px-4 py-3 rounded bg-green-50 border border-green-200 text-green-800 text-sm">
    {{ session('success') }}
</div>
@endif

{{-- Toolbar --}}
<div class="mb-5 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('medications.create') }}" class="px-4 py-2 text-sm font-semibold rounded bg-neutral-900 hover:bg-neutral-800 text-white transition">+ Add Medication</a>
</div>

{{-- Filters --}}
<div class="mb-5">
    <form method="GET" action="{{ route('medications.index') }}" class="flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ $search }}"
               class="w-full max-w-md px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none"
               placeholder="Search by name or generic name…">
        <select name="category"
                class="px-4 py-2 text-sm border border-neutral-300 dark:border-neutral-600 rounded bg-white dark:bg-neutral-800 text-neutral-900 dark:text-white focus:ring-2 focus:ring-neutral-400 outline-none">
            <option value="">All Categories</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ $category === $cat ? 'selected' : '' }}>{{ $cat }}</option>
            @endforeach
        </select>
        <button type="submit" class="px-4 py-2 text-sm font-medium rounded bg-neutral-900 hover:bg-neutral-800 text-white transition">Filter</button>
        @if($search || $category)
            <a href="{{ route('medications.index') }}" class="text-sm text-neutral-500 hover:text-neutral-700">Clear</a>
        @endif
    </form>
</div>

@if($medications->isEmpty())
    <div class="rounded border border-dashed border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 p-8 text-center text-neutral-500 dark:text-neutral-400 text-sm">
        No medications found.
    </div>
@else
    <div class="rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-900 overflow-hidden mb-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-neutral-200 dark:border-neutral-700 bg-neutral-50 dark:bg-neutral-800">
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300 w-12">#</th>
                        <th class="px-5 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Generic Name</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Category</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Form</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Strength</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Route</th>
                        <th class="px-4 py-3 text-left font-semibold text-neutral-600 dark:text-neutral-300">Frequency</th>
                        <th class="px-4 py-3 text-center font-semibold text-neutral-600 dark:text-neutral-300">Controlled</th>
                        <th class="px-4 py-3 text-center font-semibold text-neutral-600 dark:text-neutral-300">Status</th>
                        <th class="px-4 py-3 text-center font-semibold text-neutral-600 dark:text-neutral-300">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                    @foreach($medications as $med)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition">
                        <td class="px-4 py-3 text-neutral-500 dark:text-neutral-400 tabular-nums">{{ $med->id }}</td>
                        <td class="px-5 py-3 font-medium text-neutral-900 dark:text-white whitespace-nowrap">{{ $med->name }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">{{ $med->generic_name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-neutral-100 dark:bg-neutral-700 text-neutral-700 dark:text-neutral-300">
                                {{ $med->category }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">{{ $med->form }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">{{ $med->strength ?? '—' }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">{{ $med->default_route ?? '—' }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">{{ $med->default_frequency ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            @if($med->is_controlled)
                                <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded-full bg-red-100 text-red-700">Yes</span>
                            @else
                                <span class="text-neutral-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($med->is_active)
                                <span class="inline-block w-2 h-2 rounded-full bg-green-500" title="Active"></span>
                            @else
                                <span class="inline-block w-2 h-2 rounded-full bg-neutral-400" title="Inactive"></span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('medications.show', $med) }}" class="p-1.5 rounded hover:bg-neutral-100 dark:hover:bg-neutral-700 text-neutral-500 hover:text-neutral-800 transition" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="{{ route('medications.edit', $med) }}" class="p-1.5 rounded hover:bg-blue-50 dark:hover:bg-blue-900/30 text-neutral-500 hover:text-blue-600 transition" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <form method="POST" action="{{ route('medications.destroy', $med) }}" onsubmit="return confirm('Delete this medication?')" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded hover:bg-red-50 dark:hover:bg-red-900/30 text-neutral-500 hover:text-red-600 transition" title="Delete">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $medications->links() }}
    </div>
@endif

@endsection
