<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Reception Portal — Anthu Omwe Health Center')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a'
                        }
                    }
                }
            }
        };
    </script>
    <style>
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 dark:bg-neutral-950 text-neutral-800 dark:text-neutral-100 antialiased">

    <div class="min-h-screen bg-[radial-gradient(circle_at_top,_rgba(120,120,120,0.10),_transparent_45%)] dark:bg-[radial-gradient(circle_at_top,_rgba(120,120,120,0.14),_transparent_45%)]">
        <header class="sticky top-0 z-40 border-b border-white/70 dark:border-neutral-800 bg-white/85 dark:bg-neutral-900/85 backdrop-blur">
            <div class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 h-16 flex items-center justify-between gap-4">

                {{-- LEFT: Brand --}}
                <div class="flex items-center gap-2.5 min-w-0 shrink-0">
                    <div class="w-9 h-9 rounded-xl bg-neutral-900 text-white flex items-center justify-center shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                    </div>
                    <div class="hidden sm:block min-w-0">
                        <p class="text-sm font-semibold text-slate-900 dark:text-slate-100 leading-none">Anthu Omwe</p>
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">Health Center</p>
                    </div>
                </div>

                {{-- CENTER: Primary Nav --}}
                <nav class="flex items-center gap-2">

                    {{-- Search Patient --}}
                    <a href="{{ route('registration.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-semibold
                              bg-neutral-900 hover:bg-neutral-800 text-white shadow-sm transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        Search Patient
                    </a>

                    {{-- Services Queue Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                                class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                                       border border-neutral-200 dark:border-neutral-700
                                       text-neutral-700 dark:text-neutral-200
                                       hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                            <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            Services Queue
                            <svg class="w-3.5 h-3.5 text-slate-400 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>

                        <div x-show="open" x-transition
                             class="absolute left-0 mt-2 w-52 rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-slate-700 shadow-lg py-1 z-50">

                            <p class="px-3 pt-1.5 pb-1 text-[10px] font-semibold uppercase tracking-widest text-slate-400">Queues</p>

                            <a href="{{ route('triage.queue') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                <span class="w-2 h-2 rounded-full bg-neutral-400"></span>
                                Triage Queue
                            </a>
                            <a href="{{ route('screening.queue') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                <span class="w-2 h-2 rounded-full bg-neutral-500"></span>
                                Screening Queue
                            </a>
                            <a href="{{ route('lab.queue') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                <span class="w-2 h-2 rounded-full bg-neutral-600"></span>
                                Lab Queue
                            </a>
                            <a href="{{ route('screening-review.queue') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                <span class="w-2 h-2 rounded-full bg-neutral-500"></span>
                                Screening Review
                            </a>
                            <a href="{{ route('pharmacy.queue') }}"
                               class="flex items-center gap-2.5 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                <span class="w-2 h-2 rounded-full bg-neutral-700"></span>
                                Pharmacy Queue
                            </a>
                        </div>
                    </div>

                    {{-- Report --}}
                    <a href="{{ route('encounters.index') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-medium
                              border border-slate-200 dark:border-slate-700
                              text-slate-700 dark:text-slate-200
                              hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Report
                    </a>
                </nav>

                {{-- RIGHT: Notifications + User --}}
                <div class="flex items-center gap-1 shrink-0">

                    {{-- Notifications --}}
                    <button class="relative p-2 rounded-lg text-slate-500 dark:text-slate-400
                                   hover:bg-slate-100 dark:hover:bg-slate-800 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </button>

                    {{-- User Dropdown --}}
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open"
                                class="w-9 h-9 rounded-full bg-neutral-900 flex items-center justify-center text-white text-xs font-bold hover:bg-neutral-800 transition ml-1">
                            {{ strtoupper(substr((string)(auth()->user()->name ?? auth()->user()->email ?? 'A'), 0, 2)) }}
                        </button>

                        <div x-show="open" x-transition
                             class="absolute right-0 mt-2 w-44 rounded-xl bg-white dark:bg-gray-900 border border-slate-200 dark:border-slate-700 shadow-lg py-1 z-50">
                            <div class="px-3 py-2 border-b border-slate-100 dark:border-slate-800">
                                <p class="text-sm font-medium text-slate-800 dark:text-slate-100 truncate">{{ auth()->user()->name ?? 'Admin' }}</p>
                                <p class="text-xs text-slate-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                            </div>
                            <a href="{{ route('dashboard') }}"
                               class="flex items-center gap-2 px-3 py-2 text-sm text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800 transition">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                Main Dashboard
                            </a>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="w-full flex items-center gap-2 px-3 py-2 text-sm text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="max-w-7xl mx-auto px-4 md:px-6 lg:px-8 py-6 md:py-8">
            @yield('content')
        </main>
    </div>

    @stack('scripts')

    {{-- Global Loading Spinner --}}
    <div id="global-spinner" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/60 dark:bg-gray-950/60 backdrop-blur-sm" style="display:none">
        <div class="flex flex-col items-center gap-3">
            <svg class="animate-spin h-10 w-10 text-neutral-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-neutral-500 dark:text-neutral-400">Loading…</span>
        </div>
    </div>
    <script>
    (function(){
        var spinner = document.getElementById('global-spinner');
        function show(){ spinner.style.display = 'flex'; }
        document.addEventListener('submit', function(e){
            if(e.target.tagName === 'FORM') show();
        });
        document.addEventListener('click', function(e){
            var a = e.target.closest('a[href]');
            if(!a) return;
            var href = a.getAttribute('href') || '';
            if(href.startsWith('#') || href.startsWith('javascript') || a.target === '_blank') return;
            show();
        });
        window.addEventListener('pageshow', function(){ spinner.style.display = 'none'; });
    })();
    </script>
</body>
</html>
