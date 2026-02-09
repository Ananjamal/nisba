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
    <style>
        body {
            font-family: 'Cairo', sans-serif;
        }
    </style>
</head>

<body class="antialiased text-primary-900 bg-white">
    <div class="min-h-screen bg-[#f8fafc] flex flex-col pt-24 pb-12 px-6">
        <!-- Header for Guest -->
        <!-- <nav class="fixed top-0 left-0 right-0 z-50 bg-primary-900 border-b border-white/10 shadow-lg py-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <a href="{{ url('/') }}" class="hover:opacity-80 transition-opacity">
                    <x-application-logo class="text-white" />
                </a>

                <div class="flex items-center gap-4">
                    @if(!request()->routeIs('login'))
                    <a href="{{ route('login') }}" class="bg-yellow-400 text-black px-6 py-3 rounded-xl font-bold hover:bg-yellow-500 transition shadow-sm">
                        {{ __('تسجيل الدخول') }}
                    </a>
                    @endif

                    @if(!request()->routeIs('register'))
                    <a href="{{ route('register') }}" class="bg-primary-700 text-white px-6 py-3 rounded-xl font-bold hover:bg-primary-600 transition shadow-sm">
                        {{ __('انضم كشريك') }}
                    </a>
                    @endif
                </div>
            </div>
        </nav> -->

        <main class="flex-1 flex flex-col justify-center items-center">
            <div class="w-full sm:max-w-3xl relative">
                {{ $slot }}
            </div>
        </main>
    </div>
</body>

</html>