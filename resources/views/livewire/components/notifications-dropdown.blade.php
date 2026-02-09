<div class="relative" x-data="{ open: false }" @click.away="open = false">
    <button @click="open = !open"
        class="relative w-12 h-12 flex items-center justify-center rounded-2xl bg-slate-50 text-slate-400 hover:bg-slate-100 hover:text-cyber-600 transition-all duration-300">
        @if($this->unreadCount > 0)
        <span class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full border-2 border-white shadow-glow-rose animate-pulse-glow"></span>
        @endif
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
        </svg>
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="absolute left-0 mt-3 w-80 bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden z-[60]"
        style="display: none;">

        <div class="p-4 border-b border-slate-50 flex items-center justify-between bg-white/50 backdrop-blur-sm sticky top-0">
            <h3 class="text-sm font-black text-slate-900">{{ __('الإشعارات') }}</h3>
            @if($this->unreadCount > 0)
            <button wire:click="markAllAsRead" class="text-[10px] font-bold text-cyber-600 hover:text-cyber-700 transition-colors">
                {{ __('تحديد الكل كمقروء') }}
            </button>
            @endif
        </div>

        <div class="max-h-[400px] overflow-y-auto">
            @forelse($this->notifications as $notification)
            <button wire:click="markAsRead('{{ $notification->id }}')"
                class="w-full text-right p-4 hover:bg-slate-50 transition-colors border-b border-slate-50 last:border-0 relative group">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 
                            @if(($notification->data['icon'] ?? '') == 'success') bg-primary-100 text-primary-600
                            @elseif(($notification->data['icon'] ?? '') == 'error') bg-rose-100 text-rose-600
                            @elseif(($notification->data['icon'] ?? '') == 'warning') bg-amber-100 text-amber-600
                            @elseif(($notification->data['icon'] ?? '') == 'lead') bg-cyber-100 text-cyber-600
                            @elseif(($notification->data['icon'] ?? '') == 'withdrawal') bg-purple-100 text-purple-600
                            @else bg-blue-100 text-blue-600 @endif">

                        @if(($notification->data['icon'] ?? '') == 'lead')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                        @elseif(($notification->data['icon'] ?? '') == 'withdrawal')
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        @else
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold text-slate-900 group-hover:text-cyber-600 transition-colors">
                            {{ $notification->data['title'] ?? 'إشعار جديد' }}
                        </p>
                        <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">
                            {{ $notification->data['message'] ?? '' }}
                        </p>
                        <span class="text-[10px] font-medium text-slate-400 mt-2 block">
                            {{ $notification->created_at->diffForHumans() }}
                        </span>
                    </div>
                    @if(!$notification->read_at)
                    <div class="absolute top-4 left-4 w-2 h-2 bg-cyber-500 rounded-full"></div>
                    @endif
                </div>
            </button>
            @empty
            <div class="p-8 text-center">
                <div class="w-16 h-16 bg-slate-50 rounded-2xl flex items-center justify-center mx-auto mb-3 text-slate-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                    </svg>
                </div>
                <p class="text-sm font-bold text-slate-400">{{ __('لا توجد إشعارات جديدة') }}</p>
            </div>
            @endforelse
        </div>

        @if($this->unreadCount > 0)
        <div class="p-3 bg-slate-50 border-t border-slate-100 text-center">
            <a href="#" class="text-xs font-bold text-slate-500 hover:text-cyber-600 transition-colors">
                {{ __('عرض كل الإشعارات') }}
            </a>
        </div>
        @endif
    </div>
</div>