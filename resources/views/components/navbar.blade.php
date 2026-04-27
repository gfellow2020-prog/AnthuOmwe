{{-- ============================================================
     TOP NAVBAR
     ============================================================ --}}
<header class="sticky top-0 z-30 h-16 flex items-center justify-between gap-4 px-4 md:px-6
               bg-white dark:bg-neutral-950
               border-b border-neutral-200 dark:border-neutral-800">

    {{-- LEFT: Hamburger + Logo --}}
    <div class="flex items-center gap-3">
        {{-- Mobile hamburger --}}
        <button onclick="openSidebar()"
                class="lg:hidden p-2 rounded text-neutral-500 hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        {{-- Logo / Title --}}
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-neutral-900 dark:bg-white rounded flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white dark:text-neutral-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <span class="hidden sm:block font-bold text-gray-900 dark:text-white text-sm leading-tight">
                Anthu Omwe<br>
                <span class="text-xs font-normal text-gray-500 dark:text-gray-400">Admin Panel</span>
            </span>
        </div>
    </div>

    {{-- CENTER: Search bar --}}
    <div class="hidden md:flex flex-1 max-w-sm mx-4">
        <div class="relative w-full">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text" placeholder="Search patients, doctors, appointments..."
                   class="w-full pl-9 pr-4 py-2 text-sm bg-neutral-50 dark:bg-neutral-900
                          border border-neutral-200 dark:border-neutral-800
                          rounded text-neutral-700 dark:text-neutral-300
                          placeholder-neutral-400 dark:placeholder-neutral-500
                          focus:outline-none focus:ring-1 focus:ring-neutral-400 focus:border-neutral-400
                          transition">
        </div>
    </div>

    {{-- CLOCK: Digital watch time --}}
    <div class="hidden md:flex items-center px-3 py-1.5 bg-neutral-50 dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 rounded">
        <div id="nav-clock" class="text-xl font-bold text-neutral-900 dark:text-white" style="font-family:'Orbitron',sans-serif;letter-spacing:0.1em;">00:00:00</div>
    </div>

    {{-- RIGHT: Actions --}}
    <div class="flex items-center gap-1 md:gap-2">

        {{-- Notifications --}}
        <button class="relative p-2 rounded text-neutral-500 dark:text-neutral-400
                       hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="absolute top-1 right-1 w-2 h-2 bg-neutral-900 dark:bg-white rounded-full ring-2 ring-white dark:ring-neutral-950"></span>
        </button>

        {{-- Theme Toggle --}}
        <button onclick="toggleTheme()"
                class="p-2 rounded text-neutral-500 dark:text-neutral-400
                       hover:bg-neutral-100 dark:hover:bg-neutral-800 transition"
                title="Toggle theme">
            {{-- Sun icon (light mode) --}}
            <svg id="theme-icon-sun" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z"/>
            </svg>
            {{-- Moon icon (dark mode) --}}
            <svg id="theme-icon-moon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>

        {{-- Divider --}}
        <div class="w-px h-6 bg-neutral-200 dark:bg-neutral-700 mx-1"></div>

        {{-- User Dropdown --}}
        <div class="relative">
            <button id="user-dropdown-btn" onclick="toggleUserDropdown()"
                    class="flex items-center gap-2 p-1.5 rounded
                           hover:bg-neutral-100 dark:hover:bg-neutral-800 transition">
                <div class="w-8 h-8 rounded bg-neutral-900 dark:bg-white flex items-center justify-center text-white dark:text-neutral-900 text-xs font-bold">
                    {{ strtoupper(substr((string) (auth()->user()->name ?? auth()->user()->email ?? 'A'), 0, 2)) }}
                </div>
                <div class="hidden md:block text-left">
                    <div class="text-sm font-medium text-neutral-700 dark:text-neutral-200 leading-none">
                        {{ auth()->user()->name ?? 'Admin' }}
                    </div>
                    <div class="text-xs text-neutral-400 dark:text-neutral-500 mt-0.5">
                        Administrator
                    </div>
                </div>
                <svg class="hidden md:block w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Dropdown Menu --}}
            <div id="user-dropdown"
                 class="hidden absolute right-0 top-full mt-2 w-52
                        bg-white dark:bg-neutral-900
                        border border-neutral-200 dark:border-neutral-800
                        rounded shadow-lg py-1 z-50">
                <div class="px-4 py-2.5 border-b border-neutral-100 dark:border-neutral-800">
                    <p class="text-sm font-medium text-neutral-800 dark:text-neutral-100">{{ auth()->user()->name ?? 'Admin User' }}</p>
                    <p class="text-xs text-neutral-500 dark:text-neutral-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                </div>
                <a href="#" class="flex items-center gap-2 px-4 py-2.5 text-sm text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    My Profile
                </a>
                <a href="#" class="flex items-center gap-2 px-4 py-2.5 text-sm text-neutral-600 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-800 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Settings
                </a>
                <div class="border-t border-neutral-100 dark:border-neutral-800 mt-1 pt-1">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="w-full flex items-center gap-2 px-4 py-2.5 text-sm text-neutral-600 dark:text-neutral-400
                                       hover:bg-neutral-50 dark:hover:bg-neutral-800 transition text-left">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Sign Out
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

<link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap" rel="stylesheet">
<script>
(function(){
    var clockEl = document.getElementById('nav-clock');
    if (!clockEl) return;
    function pad(n){ return n < 10 ? '0' + n : n; }
    function tick(){
        var now = new Date();
        clockEl.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    }
    tick();
    setInterval(tick, 1000);
})();
</script>
