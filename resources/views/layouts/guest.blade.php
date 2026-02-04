<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

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

<body class="antialiased text-blue-900 bg-white">
    <div class="min-h-screen flex flex-col justify-center items-center p-6 sm:pt-0">
        <!-- Header for Guest -->
        <div class="mb-8 w-full max-w-7xl mx-auto px-6 py-6 flex justify-between items-center fixed top-0 left-0 right-0">
            <div class="flex items-center space-x-2 rtl:space-x-reverse">
                <div class="w-10 h-10 bg-yellow-400 rounded-xl flex items-center justify-center font-black text-2xl shadow-sm">%</div>
                <span class="text-2xl font-black text-blue-900">نسبة</span>
            </div>
            <div class="flex items-center space-x-4 rtl:space-x-reverse">
                <a href="{{ route('login') }}" class="bg-yellow-400 text-black px-6 py-3 rounded-xl font-bold hover:bg-yellow-500 transition shadow-sm">{{ __('تسجيل الدخول') }}</a>
                <a href="{{ route('register') }}" class="bg-blue-900 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-800 transition shadow-sm">{{ __('انضم كشريك') }}</a>
            </div>
        </div>

        <div class="w-full sm:max-w-xl mt-16">
            {{ $slot }}
        </div>
    </div>
</body>

</html>