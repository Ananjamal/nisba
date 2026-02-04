<?php

use Livewire\Volt\Component;

new class extends Component {
    public $notifications = [];

    public function mount()
    {
        $this->loadNotifications();
    }

    public function loadNotifications()
    {
        $this->notifications = auth()->user()->notifications()->take(5)->get();
    }

    public function markAsRead($id)
    {
        auth()->user()->unreadNotifications->where('id', $id)->markAsRead();
        $this->loadNotifications();
    }
}; ?>

<div x-data="{ open: false }" class="relative">
    <button @click="open = !open" class="p-2 text-gray-400 hover:text-gray-500 transition relative">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></span>
    </button>

    <div x-show="open" @click.away="open = false"
        class="absolute left-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 z-50 overflow-hidden"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100">
        <div class="p-4 border-b border-gray-50 flex justify-between items-center bg-gray-50/50">
            <span class="font-bold text-gray-900">{{ __('الإشعارات') }}</span>
            <span class="text-xs text-gray-400">{{ auth()->user()->unreadNotifications->count() }} {{ __('جديد') }}</span>
        </div>
        <div class="max-h-96 overflow-y-auto">
            @forelse($notifications as $notification)
            <div @click="markAsRead('{{ $notification->id }}')"
                class="p-4 border-b border-gray-50 hover:bg-gray-50 transition cursor-pointer {{ $notification->read_at ? 'opacity-60' : '' }}">
                <p class="text-sm font-bold text-gray-900">{{ $notification->data['title'] ?? 'تنبيه جديد' }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $notification->data['message'] ?? '' }}</p>
                <p class="text-[10px] text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
            </div>
            @empty
            <div class="p-8 text-center text-gray-400">
                <p class="text-sm italic">{{ __('لا توجد إشعارات حالياً') }}</p>
            </div>
            @endforelse
        </div>
    </div>
</div>