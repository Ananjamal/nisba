<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component
{
    public function logout(Logout $logout): void
    {
        $logout();
        $this->redirect('/', navigate: true);
    }
}; ?>

<nav x-data="{ open: false }" class="nav-modern py-4 px-4 sm:px-6 lg:px-8">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto">
        <div class="neo-card !bg-white/90 px-6 sm:px-8 !shadow-soft-md">
            <div class="flex justify-between h-20">
                <div class="flex items-center gap-12">
                    <!-- Logo -->
                    <div class="shrink-0 flex items-center">
                        <a href="{{ route('dashboard') }}" wire:navigate class="group flex items-center gap-4">
                            <div class="w-14 h-14 bg-gradient-to-br from-cyber-600 via-cyber-500 to-neon-purple-600 text-white rounded-2xl flex items-center justify-center shadow-glow-cyber group-hover:shadow-glow-purple group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 animate-float-slow">
                                <svg class="w-8 h-8 drop-shadow-lg" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2L4.5 20.29l.71.71L12 18l6.79 3 .71-.71L12 2z" />
                                </svg>
                            </div>
                            <div class="flex flex-col leading-tight hidden xs:flex">
                                <span class="text-2xl font-black bg-gradient-to-r from-deep-blue-900 to-cyber-600 bg-clip-text text-transparent tracking-tight">نسبة</span>
                                <span class="text-[9px] text-cyber-600 font-bold tracking-wider uppercase opacity-80">Affiliate Pro</span>
                            </div>
                        </a>
                    </div>

                    <!-- Navigation Links -->
                    <div class="hidden md:flex sm:items-center gap-2">
                        <a href="{{ route('dashboard') }}" wire:navigate
                            class="{{ request()->routeIs('dashboard') ? 'nav-link-active' : 'nav-link' }}">
                            {{ __('لوحة التحكم') }}
                        </a>

                        <a href="{{ route('affiliate.team') }}" wire:navigate
                            class="{{ request()->routeIs('affiliate.team') ? 'nav-link-active' : 'nav-link' }}">
                            {{ __('فريقي') }}
                        </a>

                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" wire:navigate
                            class="btn-glow !px-5 !py-2.5 !rounded-xl !text-sm group">
                            <div class="w-1.5 h-1.5 bg-white/80 rounded-full group-hover:scale-125 transition-transform duration-500"></div>
                            {{ __('الإدارة') }}
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Settings Dropdown -->
                <div class="hidden sm:flex sm:items-center gap-6">
                    <!-- Notifications Dropdown -->
                    <x-dropdown align="right" width="96" contentClasses="bg-white shadow-2xl border border-deep-blue-100 rounded-2xl">
                        <x-slot name="trigger">
                            <button class="relative p-3 text-deep-blue-400 hover:text-cyber-600 hover:bg-cyber-50 rounded-xl transition-all duration-300 group">
                                <span class="absolute top-2.5 right-2.5 w-2 h-2 bg-rose-accent-500 rounded-full border-2 border-white shadow-glow-rose animate-pulse-glow"></span>
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-5 py-4 border-b border-deep-blue-50">
                                <p class="text-sm font-bold text-deep-blue-900">{{ __('الإشعارات') }}</p>
                                <p class="text-xs text-deep-blue-400 mt-1">{{ __('لديك 3 إشعارات جديدة') }}</p>
                            </div>

                            <div class="max-h-96 overflow-y-auto">
                                <!-- Notification Item -->
                                <a href="#" class="flex items-start gap-4 px-5 py-4 hover:bg-cyber-50 transition-colors duration-200 border-b border-deep-blue-50/50">
                                    <div class="w-10 h-10 bg-gradient-to-br from-electric-500 to-electric-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-soft">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-deep-blue-900">{{ __('تم قبول عميل جديد') }}</p>
                                        <p class="text-xs text-deep-blue-500 mt-1">{{ __('تم إضافة العميل أحمد محمد بنجاح') }}</p>
                                        <span class="text-[10px] text-deep-blue-400 mt-2 block">{{ __('منذ 5 دقائق') }}</span>
                                    </div>
                                </a>

                                <a href="#" class="flex items-start gap-4 px-5 py-4 hover:bg-cyber-50 transition-colors duration-200 border-b border-deep-blue-50/50">
                                    <div class="w-10 h-10 bg-gradient-to-br from-amber-glow-500 to-amber-glow-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-soft">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-deep-blue-900">{{ __('عمولة جديدة') }}</p>
                                        <p class="text-xs text-deep-blue-500 mt-1">{{ __('حصلت على عمولة 500 ريال') }}</p>
                                        <span class="text-[10px] text-deep-blue-400 mt-2 block">{{ __('منذ ساعة') }}</span>
                                    </div>
                                </a>

                                <a href="#" class="flex items-start gap-4 px-5 py-4 hover:bg-cyber-50 transition-colors duration-200">
                                    <div class="w-10 h-10 bg-gradient-to-br from-cyber-500 to-cyber-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-soft">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-deep-blue-900">{{ __('تحديث النظام') }}</p>
                                        <p class="text-xs text-deep-blue-500 mt-1">{{ __('تم تحديث لوحة التحكم لإصدار 2.0') }}</p>
                                        <span class="text-[10px] text-deep-blue-400 mt-2 block">{{ __('منذ يومين') }}</span>
                                    </div>
                                </a>
                            </div>

                            <div class="px-5 py-3 bg-deep-blue-50/30 border-t border-deep-blue-100">
                                <a href="#" class="text-xs font-bold text-cyber-600 hover:text-cyber-700 transition-colors">{{ __('عرض جميع الإشعارات →') }}</a>
                            </div>
                        </x-slot>
                    </x-dropdown>

                    <div class="h-6 w-px bg-deep-blue-200"></div>

                    <x-dropdown align="right" width="64">
                        <x-slot name="trigger">
                            <button class="flex items-center gap-4 p-1 group focus:outline-none">
                                <div class="text-right hidden md:block">
                                    <div class="text-sm font-bold text-deep-blue-900 leading-none mb-1 group-hover:text-cyber-600 transition-colors duration-300" x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                                    <div class="text-[9px] font-bold text-cyber-500 uppercase tracking-wider">Pro Member</div>
                                </div>
                                <div class="w-11 h-11 ring-2 ring-deep-blue-100 group-hover:ring-cyber-200 rounded-xl overflow-hidden transition-all duration-300 shadow-soft">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=06b6d4&color=fff" class="w-full h-full object-cover" />
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="px-5 py-4 border-b border-deep-blue-50">
                                <p class="text-[9px] font-bold text-deep-blue-400 uppercase tracking-wider mb-1.5">حساب العضو</p>
                                <p class="text-xs font-semibold text-deep-blue-700 truncate">{{ auth()->user()->email }}</p>
                            </div>

                            <a href="{{ route('profile') }}" wire:navigate class="dropdown-item">
                                <svg class="w-4 h-4 opacity-60" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                {{ __('الملف الشخصي') }}
                            </a>

                            <div class="px-2 py-1.5">
                                <button wire:click="logout" class="w-full flex items-center gap-3 py-3 px-4 text-xs font-bold text-rose-accent-600 hover:bg-rose-accent-50 rounded-xl transition-all duration-200">
                                    <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    {{ __('تسجيل الخروج') }}
                                </button>
                            </div>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Hamburger -->
                <div class="-me-2 flex items-center sm:hidden">
                    <button @click="open = ! open" class="w-11 h-11 flex items-center justify-center rounded-xl text-deep-blue-500 hover:bg-deep-blue-50 focus:outline-none transition-all duration-300">
                        <svg class="h-5 w-5" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                            <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div x-show="open"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 -translate-y-4"
        x-transition:enter-end="opacity-100 translate-y-0"
        class="sm:hidden mt-4 overflow-hidden">
        <div class="neo-card !bg-white/95 !rounded-3xl p-5 shadow-elevated mx-2">
            <div class="space-y-2">
                <a href="{{ route('dashboard') }}" wire:navigate
                    class="{{ request()->routeIs('dashboard') ? 'flex items-center px-5 py-3.5 rounded-xl font-bold bg-gradient-to-r from-cyber-100 to-cyber-50 text-cyber-700 border border-cyber-200' : 'flex items-center px-5 py-3.5 rounded-xl font-semibold text-deep-blue-600 hover:bg-deep-blue-50' }}">
                    {{ __('لوحة التحكم') }}
                </a>

                @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" wire:navigate class="flex items-center justify-center gap-3 px-5 py-3.5 rounded-xl bg-gradient-to-r from-neon-purple-600 to-neon-purple-500 text-white text-sm font-bold shadow-glow-purple">
                    {{ __('لوحة الإدارة') }}
                </a>
                @endif
            </div>

            <div class="mt-6 pt-6 border-t border-deep-blue-100 px-2">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-12 h-12 bg-gradient-to-br from-cyber-100 to-cyber-50 rounded-xl flex items-center justify-center font-black text-cyber-600 text-xl border-2 border-white shadow-soft">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-bold text-deep-blue-900 text-base leading-tight">{{ auth()->user()->name }}</div>
                        <div class="font-semibold text-[10px] text-deep-blue-400 mt-0.5 uppercase tracking-wider">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('profile') }}" wire:navigate class="flex items-center px-5 py-3 font-semibold text-deep-blue-700 hover:bg-deep-blue-50 rounded-xl">
                        {{ __('الملف الشخصي') }}
                    </a>

                    <button wire:click="logout" class="w-full text-start">
                        <div class="flex items-center px-5 py-3 font-bold text-rose-accent-600 hover:bg-rose-accent-50 rounded-xl">
                            {{ __('تسجيل الخروج') }}
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>