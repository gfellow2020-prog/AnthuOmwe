@extends('layouts.dashboard')

@section('title', 'Complaints - Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Complaints</span>
@endsection

@section('content')
@if(session('success'))
<div class="mb-5 flex items-start gap-3 p-4 rounded border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-sm">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <span>{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-neutral-900 border border-neutral-700 text-white rounded text-sm">
    <ul class="list-disc pl-4 space-y-1">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded">
        <div class="px-6 py-4 border-b border-neutral-200 dark:border-neutral-800">
            <h2 class="text-base font-semibold text-neutral-900 dark:text-neutral-100">Report a Bug or Problem</h2>
            <p class="text-sm text-neutral-500 mt-1">Tell us what happened so the platform team can investigate and fix it.</p>
        </div>

        <form method="POST" action="{{ route('complaints.store') }}" class="px-6 py-5 space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Title</label>
                <input type="text"
                       name="title"
                       value="{{ old('title') }}"
                       placeholder="Short summary of the issue"
                       class="w-full px-3 py-2 text-sm rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-neutral-400"
                       required>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Severity</label>
                    <select name="severity" class="w-full px-3 py-2 text-sm rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-neutral-400" required>
                        <option value="low" {{ old('severity') === 'low' ? 'selected' : '' }}>Low</option>
                        <option value="medium" {{ old('severity', 'medium') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="high" {{ old('severity') === 'high' ? 'selected' : '' }}>High</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Page URL (optional)</label>
                    <input type="url"
                           name="page_url"
                           value="{{ old('page_url', url()->previous()) }}"
                           placeholder="https://..."
                           class="w-full px-3 py-2 text-sm rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-neutral-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Description</label>
                <textarea name="description"
                          rows="6"
                          placeholder="Describe what happened, expected behavior, and steps to reproduce."
                          class="w-full px-3 py-2 text-sm rounded border border-neutral-300 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-900 dark:text-neutral-100 focus:outline-none focus:ring-2 focus:ring-neutral-400"
                          required>{{ old('description') }}</textarea>
            </div>

            <div class="pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-900 hover:bg-neutral-700 text-white text-sm font-medium rounded transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h6m5 10H6a2 2 0 01-2-2V6a2 2 0 012-2h7.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V20a2 2 0 01-2 2z"/></svg>
                    Submit Complaint
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded">
        <div class="px-5 py-4 border-b border-neutral-200 dark:border-neutral-800">
            <h3 class="text-sm font-semibold text-neutral-900 dark:text-neutral-100">Your Recent Reports</h3>
        </div>
        <div class="p-4 space-y-3">
            @forelse($complaints as $complaint)
            <div class="border border-neutral-200 dark:border-neutral-700 rounded p-3">
                <p class="text-sm font-medium text-neutral-900 dark:text-neutral-100">{{ $complaint->title }}</p>
                <p class="text-xs text-neutral-500 mt-1">
                    {{ ucfirst($complaint->severity) }} severity
                    .
                    {{ ucfirst($complaint->status) }}
                    .
                    {{ $complaint->created_at->format('d M Y H:i') }}
                </p>
                @if($complaint->page_url)
                <a href="{{ $complaint->page_url }}" target="_blank" rel="noopener" class="mt-1 inline-block text-xs text-neutral-600 hover:text-neutral-900 underline">{{ $complaint->page_url }}</a>
                @endif
            </div>
            @empty
            <p class="text-sm text-neutral-500">No complaints submitted yet.</p>
            @endforelse
        </div>

        @if($complaints->hasPages())
        <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
            {{ $complaints->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
