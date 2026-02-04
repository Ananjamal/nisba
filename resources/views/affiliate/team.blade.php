<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-[#1e293b] leading-tight">
            {{ __('بناء الفريق والعمولات الإضافية') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <livewire:affiliate.team />
        </div>
    </div>
</x-app-layout>