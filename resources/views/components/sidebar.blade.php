{{-- ============================================================
     LEFT SIDEBAR
     ============================================================ --}}
<aside id="sidebar"
       class="fixed lg:static inset-y-0 left-0 z-40
              w-72 flex flex-col flex-shrink-0
              bg-neutral-950 dark:bg-neutral-950
              border-r border-neutral-800
              -translate-x-full lg:translate-x-0
              overflow-hidden">

    {{-- Sidebar Header --}}
    <div class="h-16 flex items-center justify-between px-4
                border-b border-neutral-800 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-white rounded flex items-center justify-center">
                <svg class="w-5 h-5 text-neutral-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-tight">Anthu Omwe Health Center</p>
                <p class="text-neutral-500 text-xs">v1.0.0</p>
            </div>
        </div>
        <button onclick="closeSidebar()" class="lg:hidden text-neutral-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Scrollable nav area --}}
    <nav id="sidebar-nav" class="flex-1 overflow-y-auto py-4 px-3 space-y-1 scrollbar-hide">

        {{-- === MAIN MODULES === --}}
        <p class="text-xs font-semibold uppercase tracking-widest text-neutral-600 px-3 mb-2">Main Modules</p>

        @php
        $currentRoute = request()->route() ? request()->route()->getName() : '';
        $dashboardItem = ['route' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'];

        // Encounter cycle stages — all wired to real routes
        $cycleStages = [
            ['route' => 'encounters.index',        'label' => 'All Encounters',      'color' => 'gray',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'stage' => null],
            ['route' => 'registration.index',      'label' => '1 · Registration',    'color' => 'blue',   'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'stage' => 'registration'],
            ['route' => 'triage.queue',            'label' => '2 · Triage',          'color' => 'yellow', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z', 'stage' => 'triage'],
            ['route' => 'screening.queue',         'label' => '3 · Screening',       'color' => 'green',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'stage' => 'screening'],
            ['route' => 'lab.queue',               'label' => '4 · Lab',             'color' => 'purple', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z', 'stage' => 'lab'],
            ['route' => 'screening-review.queue',  'label' => '5 · Screening Review','color' => 'indigo', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z', 'stage' => 'screening_review'],
            ['route' => 'pharmacy.queue',          'label' => '6 · Pharmacy',        'color' => 'orange', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.78 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z', 'stage' => 'pharmacy'],
        ];

        $navItems = [
            ['route' => 'patients.index',     'label' => 'Patients',            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['route' => 'households.index',   'label' => 'Households',          'icon' => 'M3 7h18M3 12h18M3 17h18M7 7v10m5-10v10m5-10v10'],
            ['route' => 'medications.index',  'label' => 'Medications',         'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
            ['route' => '#',                  'label' => 'Doctors',             'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['route' => '#',                  'label' => 'Appointments',        'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['route' => '#',                  'label' => 'Admissions',          'icon' => 'M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2v16z'],
            ['route' => '#',                  'label' => 'Billing',             'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['route' => '#',                  'label' => 'Radiology',           'icon' => 'M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z'],
            ['route' => '#',                  'label' => 'Wards',               'icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
            ['route' => '#',                  'label' => 'Nurses',              'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['route' => '#',                  'label' => 'Staff Management',    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['route' => '#',                  'label' => 'Reports',             'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['route' => 'complaints.index',   'label' => 'Complaints',          'icon' => 'M12 9v2m0 4h.01M10.29 3.86l-8.08 14A1 1 0 003.08 20h17.84a1 1 0 00.87-1.5l-8.08-14a1 1 0 00-1.74 0z'],
        ];
        @endphp

        @php
        $isDashboardActive = ($currentRoute === 'dashboard');
        @endphp
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded text-sm font-medium transition group
                  {{ $isDashboardActive
                      ? 'bg-white text-neutral-900'
                      : 'text-neutral-400 hover:bg-neutral-800 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ $isDashboardActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $dashboardItem['icon'] }}"/>
            </svg>
            <span>Dashboard</span>
        </a>

        {{-- === ENCOUNTER CYCLE === --}}
        <div class="pt-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-neutral-600 px-3 mb-2">Encounter Cycle</p>
            @foreach($cycleStages as $stage)
            @php
                $isActive = $currentRoute === $stage['route'];
                $badgeCount = ($stage['stage'] && isset($stageCounts[$stage['stage']])) ? $stageCounts[$stage['stage']] : 0;
            @endphp
            <a href="{{ route($stage['route']) }}"
               class="flex items-center gap-3 px-3 py-2 rounded text-sm font-medium transition group
                      {{ $isActive ? 'bg-white text-neutral-900' : 'text-neutral-400 hover:bg-neutral-800 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ $isActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $stage['icon'] }}"/>
                </svg>
                <span class="flex-1">{{ $stage['label'] }}</span>
                @if($badgeCount > 0)
                    <span class="min-w-[20px] h-5 flex items-center justify-center px-1.5 text-[10px] font-bold rounded-full {{ $isActive ? 'bg-neutral-900 text-white' : 'bg-neutral-700 text-neutral-200' }}">{{ $badgeCount }}</span>
                @endif
            </a>
            {{-- Triage sub-links --}}
            @if($stage['route'] === 'triage.queue')
                @php $isVitalsActive = $currentRoute === 'triage.vitals'; @endphp
                <a href="{{ route('triage.vitals') }}"
                   class="flex items-center gap-3 pl-10 pr-3 py-1.5 rounded text-sm font-medium transition group
                          {{ $isVitalsActive ? 'bg-white text-neutral-900' : 'text-neutral-500 hover:bg-neutral-800 hover:text-white' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $isVitalsActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span>Vitals</span>
                </a>
                @php $isMedsActive = $currentRoute === 'triage.startup-medications'; @endphp
                <a href="{{ route('triage.startup-medications') }}"
                   class="flex items-center gap-3 pl-10 pr-3 py-1.5 rounded text-sm font-medium transition group
                          {{ $isMedsActive ? 'bg-white text-neutral-900' : 'text-neutral-500 hover:bg-neutral-800 hover:text-white' }}">
                    <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $isMedsActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                    </svg>
                    <span>Startup Medications</span>
                </a>
            @endif
            @endforeach
        </div>

        {{-- === MAIN MODULES (continued) === --}}
        <div class="pt-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-neutral-600 px-3 mb-2">Main Modules</p>
            @foreach($navItems as $item)
                @php
                    $isActive = ($item['route'] !== '#' && $currentRoute === $item['route']);
                @endphp
                <a href="{{ $item['route'] !== '#' ? route($item['route']) : '#' }}"
                   class="flex items-center gap-3 px-3 py-2.5 rounded text-sm font-medium transition group
                          {{ $isActive
                              ? 'bg-white text-neutral-900'
                              : 'text-neutral-400 hover:bg-neutral-800 hover:text-white' }}">
                    <svg class="w-5 h-5 flex-shrink-0 {{ $isActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                    </svg>
                    <span>{{ $item['label'] }}</span>
                </a>
            @endforeach
        </div>

        {{-- === TOOLS === --}}
        @php
            $unreadNotifCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
            $isNotifActive    = $currentRoute === 'notifications.index';
            $isCalendarActive = str_starts_with($currentRoute, 'calendar.');
        @endphp
        <div class="pt-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-neutral-600 px-3 mb-2">Tools</p>

            {{-- Notifications --}}
            <a href="{{ route('notifications.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded text-sm font-medium transition group
                      {{ $isNotifActive ? 'bg-white text-neutral-900' : 'text-neutral-400 hover:bg-neutral-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ $isNotifActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="flex-1">Notifications</span>
                @if($unreadNotifCount > 0)
                <span class="min-w-[20px] h-5 flex items-center justify-center px-1.5 text-[10px] font-bold rounded-full {{ $isNotifActive ? 'bg-neutral-900 text-white' : 'bg-white text-neutral-900' }}">{{ $unreadNotifCount }}</span>
                @endif
            </a>

            {{-- Calendar & Events --}}
            <a href="{{ route('calendar.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded text-sm font-medium transition group
                      {{ $isCalendarActive ? 'bg-white text-neutral-900' : 'text-neutral-400 hover:bg-neutral-800 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ $isCalendarActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Calendar &amp; Events</span>
            </a>
        </div>

    </nav>

    {{-- Settings — pinned to bottom --}}
    <div class="border-t border-neutral-800 px-3 py-2 flex-shrink-0">
        @php
            $isSettingsActive = str_starts_with($currentRoute, 'settings.');
            $isUsersActive    = $currentRoute === 'settings.users.index';
        @endphp
        <a href="{{ route('settings.users.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded text-sm font-medium transition group
                  {{ $isSettingsActive ? 'bg-white text-neutral-900' : 'text-neutral-400 hover:bg-neutral-800 hover:text-white' }}">
            <svg class="w-5 h-5 flex-shrink-0 {{ $isSettingsActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span>Settings</span>
        </a>
        {{-- Settings sub-links --}}
        <a href="{{ route('settings.users.index') }}"
           class="flex items-center gap-3 pl-10 pr-3 py-1.5 rounded text-sm font-medium transition group
                  {{ $isUsersActive ? 'bg-white text-neutral-900' : 'text-neutral-500 hover:bg-neutral-800 hover:text-white' }}">
            <svg class="w-3.5 h-3.5 flex-shrink-0 {{ $isUsersActive ? 'text-neutral-900' : 'text-neutral-500 group-hover:text-white' }}"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                      d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <span>Users</span>
        </a>
    </div>

    {{-- Sidebar Footer --}}
    <div class="border-t border-neutral-800 px-4 py-3 flex-shrink-0">
        <p class="text-xs text-neutral-600 text-center">&copy; {{ date('Y') }} Anthu Omwe Health Center</p>
    </div>

</aside>

<script>
(function(){
    var nav = document.getElementById('sidebar-nav');
    if(!nav) return;
    var key = 'sidebar-scroll-top';
    var saved = sessionStorage.getItem(key);
    if(saved) nav.scrollTop = parseInt(saved, 10);
    nav.addEventListener('scroll', function(){ sessionStorage.setItem(key, nav.scrollTop); });
})();
</script>
