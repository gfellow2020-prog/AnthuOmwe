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
                    borderRadius: {
                        DEFAULT: '0.25rem',
                        'sm': '0.125rem',
                        'md': '0.25rem',
                        'lg': '0.375rem',
                        'xl': '0.5rem',
                    },
                    colors: {
                        primary: { 50:'#fafafa',100:'#f5f5f5',200:'#e5e5e5',300:'#d4d4d4',400:'#a3a3a3',500:'#737373',600:'#525252',700:'#404040',800:'#262626',900:'#171717',950:'#0a0a0a' },
                    }
                }
            }
        }
    </script>
    <style>
        /* Sidebar transition */
        #sidebar { transition: transform 0.3s ease; }
        /* Force stable scrollbar to prevent layout shift */
        html { overflow-y: scroll; overscroll-behavior: none; }
        body { overscroll-behavior: none; }
        main.flex-1.overflow-y-auto { overflow-y: scroll; overscroll-behavior: contain; }
        /* Scrollbar thin */
        ::-webkit-scrollbar { width: 5px; height: 5px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #d4d4d4; border-radius: 9999px; }
        .dark ::-webkit-scrollbar-thumb { background: #404040; }
        /* Hide scrollbar for sidebar */
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* ===== GLOBAL BLACK & WHITE THEME OVERRIDES ===== */
        /* Badges - Monochrome */
        .badge { display:inline-flex;align-items:center;padding:2px 10px;border-radius:4px;font-size:11px;font-weight:600; }
        .badge-completed { background:#171717;color:#fff; }
        .badge-locked    { background:#404040;color:#fff; }
        .badge-progress, .badge-in_progress { background:#525252;color:#fff; }
        .badge-queued    { background:#e5e5e5;color:#171717;border:1px solid #d4d4d4; }
        .badge-default   { background:#f5f5f5;color:#525252;border:1px solid #e5e5e5; }
        .badge-urgent    { background:#404040;color:#fff; }
        .badge-emergency { background:#171717;color:#fff; }
        .badge-normal    { background:#f5f5f5;color:#525252;border:1px solid #e5e5e5; }

        /* Buttons - Black */
        .btn-primary { display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:13px;font-weight:600;
                       background:#171717;color:#fff;border-radius:4px;border:none;cursor:pointer;transition:all .15s; }
        .btn-primary:hover { background:#404040; }
        .btn-success { display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:13px;font-weight:600;
                       background:#171717;color:#fff;border-radius:4px;border:none;cursor:pointer;transition:all .15s; }
        .btn-success:hover { background:#404040; }
        .btn-secondary { display:inline-flex;align-items:center;gap:6px;padding:8px 18px;font-size:13px;font-weight:600;
                         background:#f5f5f5;color:#171717;border-radius:4px;border:1px solid #d4d4d4;cursor:pointer;transition:all .15s; }
        .btn-secondary:hover { background:#e5e5e5; }

        /* Cards - Clean borders */
        .card { background:#fff;border:1px solid #d4d4d4;border-radius:4px; }
        .dark .card { background:#171717;border-color:#404040; }

        /* Form inputs */
        input[type="text"], input[type="email"], input[type="password"], input[type="search"], input[type="number"],
        input[type="tel"], input[type="date"], select, textarea {
            border-color: #d4d4d4 !important;
            border-radius: 4px !important;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #171717 !important;
            ring-color: #171717 !important;
            outline: none !important;
            box-shadow: 0 0 0 2px rgba(23,23,23,0.1) !important;
        }

        /* Tables */
        th { color: #525252 !important; }

        /* Links */
        a.text-blue-600, a.text-blue-500 { color: #171717 !important; }
        a.text-blue-600:hover, a.text-blue-500:hover { color: #404040 !important; text-decoration: underline; }
    </style>
    <style>[x-cloak] { display: none !important; }</style>
    @stack('styles')
</head>
<body class="bg-white dark:bg-neutral-950 text-neutral-800 dark:text-neutral-100 antialiased">

    <div class="flex h-screen overflow-hidden">

        {{-- ===== SIDEBAR ===== --}}
        @include('components.sidebar')

        {{-- ===== MAIN PANEL ===== --}}
        <div id="mainPanel" class="flex flex-col flex-1 overflow-hidden relative">

            {{-- ===== NAVBAR ===== --}}
            @include('components.navbar')

            {{-- Floating navbar extras --}}
            @yield('navbar-extras')

            {{-- ===== CONTENT ===== --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-6 lg:p-8">
                {{-- Breadcrumbs --}}
                @hasSection('breadcrumbs')
                <nav class="mb-5 flex items-center text-sm text-neutral-400" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="hover:text-neutral-700 transition">Home</a>
                    @yield('breadcrumbs')
                </nav>
                @endif

                {{-- Page actions (optional right-aligned buttons) --}}
                @hasSection('page-actions')
                <div class="flex items-center justify-end mb-4">
                    @yield('page-actions')
                </div>
                @endif

                {{-- Main Content --}}
                @yield('content')
            </main>

            @yield('modals')

            {{-- Global Loading Spinner (scoped to main panel) --}}
            <div id="global-spinner" class="absolute inset-0 z-[9999] flex items-center justify-center bg-white/60 dark:bg-neutral-950/60 backdrop-blur-sm" style="display:none">
                <div class="flex flex-col items-center gap-3">
                    <svg class="animate-spin h-10 w-10 text-neutral-900 dark:text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span class="text-sm font-medium text-gray-500 dark:text-gray-400">Loading…</span>
                </div>
            </div>
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

    <script>
    (function(){
        var spinner = document.getElementById('global-spinner');
        function show(){ spinner.style.display = 'flex'; }
        document.addEventListener('submit', function(e){
            if(e.target.tagName === 'FORM' && !e.target.dataset.ajax) show();
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
