<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Anthu Omwe Health Center')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a8a' },
                    }
                }
            }
        }
    </script>
    <style>
        /* Sidebar transition */
        #sidebar { transition: transform 0.3s ease; }
        /* Scrollbar thin */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #475569; }
    </style>
    @stack('styles')
</head>
<body class="bg-gray-50 dark:bg-gray-950 text-gray-800 dark:text-gray-100 antialiased">

    <div class="flex h-screen overflow-hidden">

        {{-- ===== SIDEBAR ===== --}}
        @include('components.sidebar')

        {{-- ===== MAIN PANEL ===== --}}
        <div class="flex flex-col flex-1 overflow-hidden">

            {{-- ===== NAVBAR ===== --}}
            @include('components.navbar')

            {{-- ===== CONTENT ===== --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                {{-- Page Header --}}
                @hasSection('page-header')
                    @yield('page-header')
                @endif

                {{-- Main Content --}}
                @yield('content')
            </main>

        </div>
    </div>

    {{-- Overlay for mobile sidebar --}}
    <div id="sidebar-overlay" onclick="closeSidebar()" class="fixed inset-0 bg-black/50 z-20 hidden lg:hidden"></div>

    <script>
        // ===== THEME =====
        (function () {
            const saved = localStorage.getItem('hms-theme') || 'light';
            document.documentElement.classList.toggle('dark', saved === 'dark');
        })();

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('hms-theme', isDark ? 'dark' : 'light');
            document.getElementById('theme-icon-sun').classList.toggle('hidden', isDark);
            document.getElementById('theme-icon-moon').classList.toggle('hidden', !isDark);
        }

        // ===== SIDEBAR =====
        function openSidebar() {
            document.getElementById('sidebar').classList.remove('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.remove('hidden');
        }
        function closeSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.add('hidden');
        }

        // ===== USER DROPDOWN =====
        function toggleUserDropdown() {
            document.getElementById('user-dropdown').classList.toggle('hidden');
        }

        // Close dropdown on outside click
        document.addEventListener('click', function(e) {
            const dd = document.getElementById('user-dropdown');
            const btn = document.getElementById('user-dropdown-btn');
            if (dd && btn && !btn.contains(e.target) && !dd.contains(e.target)) {
                dd.classList.add('hidden');
            }
        });

        // Set initial theme icons
        document.addEventListener('DOMContentLoaded', function () {
            const isDark = document.documentElement.classList.contains('dark');
            const sun = document.getElementById('theme-icon-sun');
            const moon = document.getElementById('theme-icon-moon');
            if (sun) sun.classList.toggle('hidden', isDark);
            if (moon) moon.classList.toggle('hidden', !isDark);
        });
    </script>

    @stack('scripts')

    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Global Loading Spinner --}}
    <div id="global-spinner" class="fixed inset-0 z-[9999] flex items-center justify-center bg-white/60 dark:bg-gray-950/60 backdrop-blur-sm" style="display:none">
        <div class="flex flex-col items-center gap-3">
            <svg class="animate-spin h-10 w-10 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Loading…</span>
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
