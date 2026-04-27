@extends('layouts.dashboard')

@section('title', 'Users — Settings — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-400">Settings</span>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Users</span>
@endsection

@section('page-actions')
<a href="{{ route('settings.users.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-neutral-900 hover:bg-neutral-700 text-white text-sm font-medium rounded transition">
    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
    Add User
</a>
@endsection

@section('content')

{{-- Flash messages --}}
@if(session('success'))
    <div class="mb-5 flex items-start gap-3 p-4 rounded border border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
        </svg>
        <span>{{ session('success') }}</span>
    </div>
@endif

@if(session('error'))
    <div class="mb-5 flex items-start gap-3 p-4 rounded border border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300 text-sm">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.293 4.293a1 1 0 011.414 0l7 7a1 1 0 010 1.414l-7 7a1 1 0 01-1.414 0l-7-7a1 1 0 010-1.414l7-7z"/>
        </svg>
        <span>{{ session('error') }}</span>
    </div>
@endif

{{-- Search --}}
<form method="GET" action="{{ route('settings.users.index') }}" class="mb-5 flex flex-col sm:flex-row gap-3">
    <div class="relative flex-1">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400 pointer-events-none"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="text" name="search" value="{{ $search }}"
               placeholder="Search by name or email..."
               class="w-full pl-9 pr-4 py-2.5 text-sm bg-white dark:bg-neutral-900
                      border border-neutral-300 dark:border-neutral-700 rounded
                      text-neutral-800 dark:text-neutral-200
                      placeholder-neutral-400 dark:placeholder-neutral-500
                      focus:outline-none focus:ring-2 focus:ring-neutral-400">
    </div>
    <button type="submit"
            class="px-5 py-2.5 bg-neutral-900 hover:bg-neutral-700 text-white text-sm font-medium rounded transition">
        Search
    </button>
</form>

{{-- Users Table --}}
<div class="bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-neutral-50 dark:bg-neutral-800 text-neutral-500 dark:text-neutral-400 uppercase text-xs tracking-wider">
                <tr>
                    <th class="px-4 py-3">#</th>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Email</th>
                    <th class="px-4 py-3">Created</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-neutral-100 dark:divide-neutral-800">
                @forelse($users as $user)
                    <tr class="hover:bg-neutral-50 dark:hover:bg-neutral-800/50 transition">
                        <td class="px-4 py-3 text-neutral-500">{{ $user->id }}</td>
                        <td class="px-4 py-3 font-medium text-neutral-800 dark:text-neutral-200">{{ $user->name }}</td>
                        <td class="px-4 py-3 text-neutral-600 dark:text-neutral-400">{{ $user->email }}</td>
                        <td class="px-4 py-3 text-neutral-500">{{ $user->created_at->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('settings.users.edit', $user) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded
                                          border border-neutral-300 dark:border-neutral-700
                                          text-neutral-700 dark:text-neutral-300
                                          hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                    Edit
                                </a>
                                @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('settings.users.destroy', $user) }}"
                                          onsubmit="return confirm('Are you sure you want to delete this user?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium rounded
                                                       border border-red-300 dark:border-red-700
                                                       text-red-600 dark:text-red-400
                                                       hover:bg-red-50 dark:hover:bg-red-900/20 transition">
                                            Delete
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-neutral-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div class="px-4 py-3 border-t border-neutral-200 dark:border-neutral-800">
            {{ $users->links() }}
        </div>
    @endif
</div>

@endsection
