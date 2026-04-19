{{-- ============================================================
     LEFT SIDEBAR
     ============================================================ --}}
<aside id="sidebar"
       class="fixed lg:static inset-y-0 left-0 z-40
              w-64 flex flex-col flex-shrink-0
              bg-gray-900 dark:bg-gray-950
              border-r border-gray-700/60
              -translate-x-full lg:translate-x-0
              overflow-hidden">

    {{-- Sidebar Header --}}
    <div class="h-16 flex items-center justify-between px-4
                border-b border-gray-700/60 flex-shrink-0">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
            </div>
            <div>
                <p class="text-white font-bold text-sm leading-tight">Anthu Omwe Health Center</p>
                <p class="text-gray-400 text-xs">v1.0.0</p>
            </div>
        </div>
        <button onclick="closeSidebar()" class="lg:hidden text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>

    {{-- Scrollable nav area --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

        {{-- === MAIN MODULES === --}}
        <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 px-3 mb-2">Main Modules</p>

        @php
        $currentRoute = request()->route() ? request()->route()->getName() : '';
        $navItems = [
            ['route' => 'dashboard',          'label' => 'Dashboard',           'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
            ['route' => 'reception.dashboard','label' => 'Reception Dashboard', 'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['route' => 'patients.index',     'label' => 'Patients',            'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['route' => 'households.index',   'label' => 'Households',          'icon' => 'M3 7h18M3 12h18M3 17h18M7 7v10m5-10v10m5-10v10'],
            ['route' => '#',                  'label' => 'Doctors',             'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
            ['route' => '#',                  'label' => 'Appointments',        'icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['route' => '#',                  'label' => 'Admissions',          'icon' => 'M19 21l-7-5-7 5V5a2 2 0 012-2h10a2 2 0 012 2v16z'],
            ['route' => '#',                  'label' => 'Billing',             'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['route' => '#',                  'label' => 'Radiology',           'icon' => 'M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z'],
            ['route' => '#',                  'label' => 'Wards',               'icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z'],
            ['route' => '#',                  'label' => 'Nurses',              'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['route' => '#',                  'label' => 'Staff Management',    'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
            ['route' => '#',                  'label' => 'Reports',             'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['route' => '#',                  'label' => 'Settings',            'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
        ];

        // Encounter cycle stages — all wired to real routes
        $cycleStages = [
            ['route' => 'encounters.index',        'label' => 'All Encounters',      'color' => 'gray',   'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'],
            ['route' => 'registration.index',      'label' => '1 · Registration',    'color' => 'blue',   'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
            ['route' => 'triage.queue',            'label' => '2 · Triage',          'color' => 'yellow', 'icon' => 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z'],
            ['route' => 'screening.queue',         'label' => '3 · Screening',       'color' => 'green',  'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ['route' => 'lab.queue',               'label' => '4 · Lab',             'color' => 'purple', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.155-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
            ['route' => 'screening-review.queue',  'label' => '5 · Screening Review','color' => 'indigo', 'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z'],
            ['route' => 'pharmacy.queue',          'label' => '6 · Pharmacy',        'color' => 'orange', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.78 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z'],
        ];

        $comingSoon = [
            'Ambulance Management','Blood Bank','Operation Theatre','Insurance Claims',
            'Inventory / Medical Supplies','Mortuary Management','Telemedicine','Bed Management',
            'Visitor Management','Asset Management','Payroll','HR & Leave',
            'Queue Management','Clinical Notes','Electronic Medical Records','Referral Management',
            'SMS / Email Notifications','Patient Portal','Doctor Portal','Audit Logs',
        ];
        @endphp

        @foreach($navItems as $item)
            @php
                $isActive = ($item['route'] !== '#' && $currentRoute === $item['route']);
            @endphp
            <a href="{{ $item['route'] !== '#' ? route($item['route']) : '#' }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition group
                      {{ $isActive
                          ? 'bg-blue-600 text-white'
                          : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0 {{ $isActive ? 'text-white' : 'text-gray-400 group-hover:text-white' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
                </svg>
                <span>{{ $item['label'] }}</span>
            </a>
        @endforeach

        {{-- === ENCOUNTER CYCLE === --}}
        <div class="pt-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 px-3 mb-2">Encounter Cycle</p>
            @foreach($cycleStages as $stage)
            @php $isActive = $currentRoute === $stage['route']; @endphp
            <a href="{{ route($stage['route']) }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition group
                      {{ $isActive ? 'bg-blue-600 text-white' : 'text-gray-300 hover:bg-gray-700/60 hover:text-white' }}">
                <svg class="w-4 h-4 flex-shrink-0 {{ $isActive ? 'text-white' : 'text-gray-400 group-hover:text-white' }}"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $stage['icon'] }}"/>
                </svg>
                <span>{{ $stage['label'] }}</span>
            </a>
            @endforeach
        </div>

        {{-- === COMING SOON SECTION === --}}
        <div class="pt-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 px-3 mb-2">Coming Soon</p>
            @foreach($comingSoon as $module)
                <div class="flex items-center justify-between px-3 py-2 rounded-lg cursor-not-allowed opacity-60 group">
                    <div class="flex items-center gap-3">
                        <div class="w-1.5 h-1.5 rounded-full bg-gray-500 flex-shrink-0"></div>
                        <span class="text-sm text-gray-400">{{ $module }}</span>
                    </div>
                    <span class="text-[10px] font-semibold bg-gray-700 text-gray-400 px-1.5 py-0.5 rounded-full uppercase tracking-wide whitespace-nowrap">
                        Soon
                    </span>
                </div>
            @endforeach
        </div>

    </nav>

    {{-- Sidebar Footer --}}
    <div class="border-t border-gray-700/60 px-4 py-3 flex-shrink-0">
        <p class="text-xs text-gray-500 text-center">&copy; {{ date('Y') }} Anthu Omwe Health Center</p>
    </div>

</aside>
