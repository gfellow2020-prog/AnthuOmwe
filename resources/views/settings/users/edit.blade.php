@extends('layouts.dashboard')

@section('title', 'Edit User — Settings — Anthu Omwe Health Center')

@section('breadcrumbs')
<span class="mx-2">/</span>
<span class="text-neutral-400">Settings</span>
<span class="mx-2">/</span>
<a href="{{ route('settings.users.index') }}" class="hover:text-neutral-700 transition">Users</a>
<span class="mx-2">/</span>
<span class="text-neutral-700 dark:text-neutral-200 font-medium">Edit {{ $user->name }}</span>
@endsection

@section('content')

<div class="max-w-xl">
    <form method="POST" action="{{ route('settings.users.update', $user) }}" class="space-y-5">
        @csrf
        @method('PUT')

        {{-- Name --}}
        <div>
            <label for="name" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Name</label>
            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                   class="w-full px-4 py-2.5 text-sm bg-white dark:bg-neutral-900
                          border border-neutral-300 dark:border-neutral-700 rounded
                          text-neutral-800 dark:text-neutral-200
                          focus:outline-none focus:ring-2 focus:ring-neutral-400">
            @error('name') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Email</label>
            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                   class="w-full px-4 py-2.5 text-sm bg-white dark:bg-neutral-900
                          border border-neutral-300 dark:border-neutral-700 rounded
                          text-neutral-800 dark:text-neutral-200
                          focus:outline-none focus:ring-2 focus:ring-neutral-400">
            @error('email') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Password (optional) --}}
        <div>
            <label for="password" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">New Password <span class="text-neutral-400 font-normal">(leave blank to keep current)</span></label>
            <input type="password" id="password" name="password"
                   class="w-full px-4 py-2.5 text-sm bg-white dark:bg-neutral-900
                          border border-neutral-300 dark:border-neutral-700 rounded
                          text-neutral-800 dark:text-neutral-200
                          focus:outline-none focus:ring-2 focus:ring-neutral-400">
            @error('password') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
        </div>

        {{-- Confirm Password --}}
        <div>
            <label for="password_confirmation" class="block text-sm font-medium text-neutral-700 dark:text-neutral-300 mb-1">Confirm New Password</label>
            <input type="password" id="password_confirmation" name="password_confirmation"
                   class="w-full px-4 py-2.5 text-sm bg-white dark:bg-neutral-900
                          border border-neutral-300 dark:border-neutral-700 rounded
                          text-neutral-800 dark:text-neutral-200
                          focus:outline-none focus:ring-2 focus:ring-neutral-400">
        </div>

        <button type="submit"
                class="px-6 py-2.5 bg-neutral-900 hover:bg-neutral-700 text-white text-sm font-medium rounded transition">
            Update User
        </button>
    </form>
</div>

@endsection
