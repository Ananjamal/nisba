<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'حليف') }} - لوحة الإدارة</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <style>
        /* Custom Premium Scrollbar for Sidebar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(15, 23, 42, 0.05);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        /* For Firefox */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.1) rgba(15, 23, 42, 0.05);
        }
    </style>
</head>

<body class="antialiased bg-gray-50 font-cairo"
    x-data="{ sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false', mobileOpen: false }"
    x-init="$watch('sidebarOpen', val => localStorage.setItem('sidebarOpen', val))">

    <!-- Mobile Sidebar System -->
    <div x-show="mobileOpen" style="display: none;" class="relative z-40 lg:hidden" aria-modal="true">
        <div x-show="mobileOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80 backdrop-blur-sm" @click="mobileOpen = false"></div>

        <div class="fixed inset-0 flex flex-row-reverse">
            <div x-show="mobileOpen" x-transition:enter="transition ease-in-out duration-300 transform" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in-out duration-300 transform" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" class="relative flex w-full max-w-xs flex-1 flex-col bg-primary-900 pt-5 pb-4 overflow-y-auto custom-scrollbar">

                <div class="absolute top-0 left-0 -ml-12 pt-2">
                    <button type="button" class="ml-1 flex h-10 w-10 items-center justify-center rounded-full focus:outline-none focus:ring-2 focus:ring-inset focus:ring-white" @click="mobileOpen = false">
                        <span class="sr-only">إغلاق القائمة</span>
                        <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="flex shrink-0 items-center px-4 gap-3">
                    <x-application-logo class="h-10 w-auto" />
                </div>

                <nav class="mt-8 flex-1 space-y-2 px-4 overflow-y-auto">
                    @foreach([
                    ['route' => 'admin.dashboard', 'title' => 'لوحة القيادة', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                    ['route' => 'admin.leads', 'title' => 'إدارة العملاء', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                    ['route' => 'admin.affiliates', 'title' => 'المسوقين', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['route' => 'admin.marketers.ranks', 'title' => 'رتب المسوقين', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                    ['route' => 'admin.payouts', 'title' => 'طلبات السحب', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                    ['route' => 'admin.services', 'title' => 'إدارة الخدمات', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a2 2 0 00-1.96 1.414l-.477 2.387a2 2 0 00.547 1.022l1.414 1.414a2 2 0 001.022.547l2.387.477a2 2 0 001.96-1.414l.477-2.387a2 2 0 00-.547-1.022l-1.414-1.414z'],
                    ['route' => 'admin.sectors', 'title' => 'إدارة القطاعات', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                    ['route' => 'admin.staff.index', 'title' => 'الموظفين', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                    ['route' => 'admin.roles.index', 'title' => 'الأدوار والصلاحيات', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    ['route' => 'admin.settings', 'title' => 'الإعدادات', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z']
                    ] as $item)
                    <a href="{{ route($item['route']) }}" class="group flex items-center px-4 py-3 text-base font-bold rounded-xl {{ request()->routeIs($item['route']) ? 'bg-primary-800 text-white' : 'text-primary-100 hover:bg-primary-800 hover:text-white' }}">
                        <svg class="ml-4 h-6 w-6 shrink-0 {{ request()->routeIs($item['route']) ? 'text-yellow-400' : 'text-primary-300 group-hover:text-yellow-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['title'] }}
                    </a>
                    @endforeach
                </nav>
            </div>

            <div class="w-14 shrink-0">
                <!-- Force sidebar to shrink to fit close icon -->
            </div>
        </div>
    </div>

    <!-- Desktop Layout -->
    <div class="min-h-screen bg-gray-50 flex flex-row-reverse relative">
        <!-- Desktop Sidebar (Fixed) -->
        <aside class="hidden lg:flex flex-col fixed inset-y-0 right-0 z-20 bg-primary-900 shadow-2xl transition-all duration-300 overflow-y-auto custom-scrollbar border-l border-white/10"
            :class="sidebarOpen ? 'w-64' : 'w-20'">

            <!-- Logo area -->
            <div class="flex h-20 shrink-0 items-center justify-between px-4 bg-primary-950 border-b border-white/10 sticky top-0 z-10 backdrop-blur-md">
                <div class="flex items-center gap-3 overflow-hidden transition-all duration-300" :class="sidebarOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0 hidden'">
                    <x-application-logo class="text-white" />
                    <div class="flex flex-col">
                        <span class="text-[10px] text-primary-300 font-bold uppercase tracking-widest mt-1">لوحة الإدارة</span>
                    </div>
                </div>
                <!-- Minimized Logo -->
                <div :class="sidebarOpen ? 'hidden' : 'flex w-full justify-center'">
                    <img src="{{ asset('images/logo-haleef.png') }}" alt="شعار حليف" class="w-10 h-10 object-contain shadow-lg">
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 flex flex-col gap-2 px-3 py-6">
                @foreach([
                ['route' => 'admin.dashboard', 'title' => 'الرئيسية', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route' => 'admin.leads', 'title' => 'إدارة العملاء', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['route' => 'admin.affiliates', 'title' => 'المسوقين', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                ['route' => 'admin.marketers.ranks', 'title' => 'رتب المسوقين', 'icon' => 'M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z'],
                ['route' => 'admin.payouts', 'title' => 'طلبات السحب', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route' => 'admin.services', 'title' => 'إدارة الخدمات', 'icon' => 'M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a2 2 0 00-1.96 1.414l-.477 2.387a2 2 0 00.547 1.022l1.414 1.414a2 2 0 001.022.547l2.387.477a2 2 0 001.96-1.414l.477-2.387a2 2 0 00-.547-1.022l-1.414-1.414z'],
                ['route' => 'admin.sectors', 'title' => 'إدارة القطاعات', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route' => 'admin.staff.index', 'title' => 'الموظفين', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                ['route' => 'admin.roles.index', 'title' => 'الأدوار والصلاحيات', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                ['route' => 'admin.settings', 'title' => 'الإعدادات', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z']
                ] as $item)
                <a href="{{ route($item['route']) }}"
                    class="group flex items-center gap-3 px-3 py-3.5 rounded-xl transition-all duration-200 relative overflow-hidden {{ request()->routeIs($item['route']) ? 'bg-primary-700 text-white shadow-lg shadow-primary-900/50 from-primary-700 to-primary-800 bg-gradient-to-l' : 'text-primary-200 hover:bg-white/5 hover:text-white' }}">

                    @if(request()->routeIs($item['route']))
                    <div class="absolute right-0 top-1/2 -translate-y-1/2 w-1 h-8 bg-primary-400 rounded-l-full shadow-[0_0_10px_rgba(34,197,94,0.5)]"></div>
                    @endif

                    <div class="shrink-0 transition-transform duration-300 group-hover:scale-110 {{ request()->routeIs($item['route']) ? 'text-primary-300' : 'text-primary-400 group-hover:text-primary-300' }}"
                        :class="sidebarOpen ? '' : 'mx-auto'">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}" />
                        </svg>
                    </div>

                    <span class="font-bold whitespace-nowrap transition-all duration-300 origin-right"
                        :class="sidebarOpen ? 'opacity-100 scale-100' : 'opacity-0 scale-90 w-0 hidden'">
                        {{ $item['title'] }}
                    </span>

                    <div x-show="!sidebarOpen" class="hidden lg:group-hover:block absolute right-14 top-1/2 -translate-y-1/2 bg-gray-900 text-white text-xs font-bold px-3 py-2 rounded-lg shadow-xl whitespace-nowrap z-[60] border border-white/10 animate-fade-in-right">
                        {{ $item['title'] }}
                        <div class="absolute top-1/2 -right-1 -translate-y-1/2 w-2 h-2 bg-gray-900 rotate-45 border-t border-r border-white/10"></div>
                    </div>
                </a>
                @endforeach
            </nav>

            <!-- User Section -->
            <div class="mt-auto p-4 border-t border-white/10 bg-primary-950/30">
                <div class="flex items-center gap-3 overflow-hidden" :class="sidebarOpen ? '' : 'justify-center'">
                    <div class="relative shrink-0">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=10B981&color=FFFFFF&bold=true"
                            class="w-10 h-10 rounded-xl border-2 border-primary-600 shadow-md transform hover:scale-105 transition-transform">
                        <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 border-2 border-primary-900 rounded-full animate-pulse"></div>
                    </div>

                    <div class="flex flex-col overflow-hidden transition-all duration-300" :class="sidebarOpen ? 'opacity-100 w-auto' : 'opacity-0 w-0 hidden'">
                        <span class="text-sm font-bold text-white truncate">{{ auth()->user()->name }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="text-xs text-primary-300 hover:text-white hover:underline mt-0.5 text-right w-full">تسجيل الخروج</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Collapse Button (Bottom) -->
            <button @click="sidebarOpen = !sidebarOpen" class="flex items-center justify-center p-2 bg-primary-950 border-t border-white/5 text-primary-400 hover:text-white hover:bg-primary-800 transition-colors">
                <svg class="w-5 h-5 transition-transform duration-300" :class="sidebarOpen ? 'rotate-0' : 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                </svg>
            </button>
        </aside>

        <!-- Main Content -->
        <main class="transition-all duration-300 ease-out flex-1 flex flex-col w-full"
            :class="sidebarOpen ? 'lg:mr-64' : 'lg:mr-20'">

            <!-- Header -->
            <header class="bg-white border-b border-gray-100 sticky top-0 z-10 shadow-sm/50 backdrop-blur-xl bg-white/90">
                <div class="px-4 sm:px-6 lg:px-8 py-4 flex items-center justify-between">
                    <div class="flex items-center gap-4">
                        <button type="button" class="lg:hidden p-2 text-gray-500 hover:text-gray-900 rounded-lg hover:bg-gray-100 transition-colors" @click="mobileOpen = true">
                            <span class="sr-only">فتح القائمة</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                        </button>

                        <div>
                            <h1 class="text-2xl font-black text-gray-900 tracking-tight">{{ $header ?? 'لوحة القيادة' }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 md:gap-4">
                        <a href="{{ route('dashboard') }}" class="hidden md:inline-flex items-center justify-center gap-2 rounded-xl border border-primary-100 bg-primary-50 px-4 py-2.5 text-sm font-bold text-primary-700 hover:bg-primary-100 hover:border-primary-200 transition-all shadow-sm">
                            <svg class="w-4 h-4 text-primary-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                            عرض كمسوق
                        </a>
                        <div class="h-8 w-px bg-gray-200 mx-1 hidden md:block"></div>
                        <livewire:components.notifications-dropdown />
                    </div>
                </div>
            </header>

            <!-- Page Content (Scrollable) -->
            <div class="flex-1 p-4 sm:p-6 lg:p-8 overflow-x-hidden">
                <div class="max-w-7xl mx-auto animate-fade-in-up">
                    {{ $slot }}
                </div>
            </div>
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast />
</body>

</html>