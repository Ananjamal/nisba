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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        /* Animated Grid Background */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image:
                linear-gradient(rgba(6, 182, 212, 0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(6, 182, 212, 0.03) 1px, transparent 1px);
            background-size: 50px 50px;
            z-index: -1;
            animation: gridMove 20s linear infinite;
        }

        @keyframes gridMove {
            0% {
                background-position: 0 0;
            }

            100% {
                background-position: 50px 50px;
            }
        }
    </style>
</head>

<body class="font-inter antialiased text-deep-blue-900 selection:bg-cyber-100 selection:text-cyber-900 overflow-x-hidden">
    <div class="min-h-screen relative">
        <!-- Modern Background Elements -->
        <div class="fixed top-0 right-0 w-[800px] h-[800px] bg-gradient-radial from-cyber-100/40 via-cyber-50/20 to-transparent rounded-full blur-3xl -z-10 opacity-60 animate-float-slow"></div>
        <div class="fixed bottom-0 left-0 w-[600px] h-[600px] bg-gradient-radial from-neon-purple-100/30 via-neon-purple-50/15 to-transparent rounded-full blur-3xl -z-10 opacity-50 animate-float"></div>
        <div class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full h-full bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white/50 via-transparent to-transparent -z-10"></div>

        <livewire:layout.navigation />

        <!-- Page Heading -->
        @if (isset($header))
        <header class="pt-8 pb-4">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
        @endif

        <!-- Page Content -->
        <main class="pb-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>

</body>

</html>