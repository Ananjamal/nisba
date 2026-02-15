<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'حليف') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>

<body class="font-cairo bg-main">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="page-header sticky top-0 z-40 bg-white shadow-sm border-b border-gray-100">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
                <!-- Logo & Navigation -->
                <div class="flex items-center gap-8">
                    <x-application-logo class="text-primary-900" />

                    <nav class="hidden md:flex items-center gap-6">
                        <a href="{{ route('dashboard') }}" class="text-sm font-semibold {{ request()->routeIs('dashboard') ? 'text-primary-600' : 'text-secondary hover:text-primary-900' }} transition">لوحة القيادة</a>
                        <a href="{{ route('affiliate.team') }}" class="text-sm font-semibold {{ request()->routeIs('affiliate.team') ? 'text-primary-600' : 'text-secondary hover:text-primary-900' }} transition">فريقي</a>
                        @role('admin')
                        <a href="{{ route('admin.dashboard') }}" class="text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'text-primary-600' : 'text-secondary hover:text-primary-900' }} transition">لوحة الإدارة</a>
                        @endrole
                    </nav>
                </div>

                <!-- User Info & Actions -->
                <div class="flex items-center gap-4">
                    <!-- Notifications -->
                    <livewire:components.notifications-dropdown />

                    <!-- User Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center gap-3 focus:outline-none hover:bg-gray-50 p-2 rounded-lg transition">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-semibold text-primary-900">{{ auth()->user()->name }}</p>
                                <p class="text-[10px] text-secondary">مسوق بالعمولة</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-bold border-2 border-white shadow-sm">
                                {{ mb_substr(auth()->user()->name, 0, 1) }}
                            </div>
                            <svg class="w-4 h-4 text-gray-400" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                            @click.away="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 scale-95"
                            x-transition:enter-end="opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="opacity-100 scale-100"
                            x-transition:leave-end="opacity-0 scale-95"
                            class="absolute left-0 mt-2 w-56 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50 origin-top-left"
                            style="display: none;">

                            <div class="px-4 py-3 border-b border-gray-100">
                                <p class="text-sm font-bold text-gray-900">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500 truncate">{{ auth()->user()->email }}</p>
                            </div>

                            <div class="py-1">
                                <a href="{{ route('profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                    الملف الشخصي
                                </a>
                                <a href="{{ route('affiliate.team') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                    فريقي
                                </a>
                                @role('admin')
                                <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 hover:text-primary-600 transition">
                                    لوحة الإدارة
                                </a>
                                @endrole

                            </div>

                            <div class="border-t border-gray-100 py-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-right px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                                        تسجيل الخروج
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    <!-- Toast Notifications -->
    <x-toast />
</body>

</html>