<div {{ $attributes->merge(['class' => 'flex items-center gap-2']) }}>
    <img src="{{ asset('images/logo-haleef.png') }}" alt="{{ config('app.name', 'حليف') }}" class="h-20 w-auto object-contain">
    <span class="text-2xl font-black tracking-tight whitespace-nowrap">{{ config('app.name', 'حليف') }}</span>
</div>