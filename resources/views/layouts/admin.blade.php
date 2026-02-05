<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - الإدارة</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased text-blue-900 bg-blue-50 flex min-h-screen font-cairo">
    <!-- Sidebar -->
    <aside class="w-72 bg-white fixed h-full z-50 shadow-sm border-l border-blue-100 transition-all duration-300">
        <div class="p-8 h-full flex flex-col justify-between">
            <div>
                <div class="flex items-center gap-3 mb-12">
                    <div class="w-10 h-10 bg-blue-900 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                        <span class="font-black text-xl text-white">%</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="text-xl font-black text-blue-900 tracking-wider">NISBA</span>
                        <span class="text-[10px] text-luxury-gold font-bold tracking-[0.2em] uppercase">Private Admin</span>
                    </div>
                </div>

                <nav class="space-y-3">
                    <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-blue-900 text-white shadow-lg' : 'text-blue-500 hover:text-blue-900 hover:bg-blue-50' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                        </svg>
                        <span class="font-bold">لوحة القيادة</span>
                    </a>
                    <a href="{{ route('admin.leads') }}" class="group flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.leads') ? 'bg-blue-900 text-white shadow-lg' : 'text-blue-500 hover:text-blue-900 hover:bg-blue-50' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="font-bold">قائمة العملاء</span>
                    </a>
                    <a href="{{ route('admin.affiliates') }}" class="group flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.affiliates') ? 'bg-blue-900 text-white shadow-lg' : 'text-blue-500 hover:text-blue-900 hover:bg-blue-50' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        <span class="font-bold">المسوقين</span>
                    </a>
                    <a href="{{ route('admin.payouts') }}" class="group flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.payouts') ? 'bg-blue-900 text-white shadow-lg' : 'text-blue-500 hover:text-blue-900 hover:bg-blue-50' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <span class="font-bold">طلبات السحب</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" class="group flex items-center gap-4 px-5 py-4 rounded-2xl transition-all duration-300 {{ request()->routeIs('admin.settings') ? 'bg-blue-900 text-white shadow-lg' : 'text-blue-500 hover:text-blue-900 hover:bg-blue-50' }}">
                        <svg class="w-6 h-6 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                        <span class="font-bold">الإعدادات</span>
                    </a>
                    
                </nav>
            </div>

            <div class="border-t border-blue-100 pt-8">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center gap-4 px-5 py-4 text-red-500 font-bold hover:text-red-600 hover:bg-red-50 rounded-2xl transition-all w-full text-right">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                        </svg>
                        <span>خروج آمن</span>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 mr-72 p-10 min-h-screen">
        <header class="flex justify-between items-center mb-12 bg-white border border-blue-100 p-6 rounded-3xl sticky top-6 z-40 shadow-sm">
            <div>
                <h2 class="text-3xl font-black text-blue-900 uppercase tracking-tight">{{ $header ?? '' }}</h2>
                <p class="text-blue-500 text-sm font-medium mt-1">Nisba Analytics Platform</p>
            </div>
            <div class="flex items-center gap-6">
                <!-- Notifications Dropdown (Admin) -->
                <x-dropdown align="right" width="150" contentClasses="bg-white shadow-2xl border border-deep-blue-100 rounded-2xl">
                    <x-slot name="trigger">
                        <button class="relative w-12 h-12 flex items-center justify-center rounded-2xl bg-blue-50 text-blue-400 hover:bg-blue-100 hover:text-cyber-600 transition-all duration-300">
                            <span class="absolute top-2 right-2 w-2 h-2 bg-rose-accent-500 rounded-full border-2 border-white shadow-glow-rose animate-pulse-glow"></span>
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                            </svg>
                        </button>
                    </x-slot>
                    <x-slot name="content">
                        <div class="p-4">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-black text-deep-blue-900">{{ __('الإشعارات') }}</h3>
                                <span class="badge-primary !px-3 !py-1 !text-xs">3 {{ __('جديد') }}</span>
                            </div>

                            <!-- Notification Item -->
                            <a href="#" class="block p-4 hover:bg-deep-blue-50 rounded-xl transition-colors">
                                <div class="flex items-start gap-3">
                                    <div class="w-10 h-10 bg-electric-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-electric-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-deep-blue-900">{{ __('عميل جديد') }}</p>
                                        <p class="text-xs text-deep-blue-500 mt-1">{{ __('تم إضافة عميل جديد بنجاح') }}</p>
                                        <p class="text-xs text-deep-blue-400 mt-1">{{ __('منذ 5 دقائق') }}</p>
                                    </div>
                                </div>
                            </a>

                            <div class="mt-3 pt-3 border-t border-deep-blue-100">
                                <a href="#" class="block text-center text-sm font-bold text-cyber-600 hover:text-cyber-700 py-2">
                                    {{ __('عرض جميع الإشعارات →') }}
                                </a>
                            </div>
                        </div>
                    </x-slot>
                </x-dropdown>

                <div class="h-12 w-[1px] bg-blue-100"></div>

                <div class="flex items-center gap-4 group cursor-pointer">
                    <div class="text-left rtl:text-right">
                        <p class="text-sm font-black text-blue-900">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-blue-400 font-bold uppercase tracking-widest leading-tight">Administrator</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 p-0.5 rounded-2xl border-2 border-transparent group-hover:border-blue-900 transition-all duration-300">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=1e3a8a&color=fff" class="w-full h-full rounded-[14px] object-cover" />
                    </div>
                </div>
            </div>
        </header>

        <div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
            {{ $slot }}
        </div>
    </main>
</body>

</html>