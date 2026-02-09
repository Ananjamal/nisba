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
                            <img src="{{ asset('images/logo-haleef.png') }}" alt="Haleef Logo" class="w-14 h-14 object-contain shrink-0 shadow-lg shadow-primary-200 transform group-hover:rotate-3 transition-transform duration-500">
                            <div class="flex flex-col leading-tight hidden xs:flex">
                                <span class="text-xl font-black text-primary-900 tracking-tight">حليف</span>
                                <span class="text-[9px] font-bold text-primary-400 uppercase tracking-widest leading-none">شريك النجاح</span>
                            </div>
                        </a>
                    </div>

                    <!-- Right Side: Links & User -->
                    <div class="hidden lg:flex lg:items-center lg:gap-8">
                        <nav class="flex items-center gap-1">
                            @foreach([
                            ['route' => 'dashboard', 'label' => 'الرئيسية', 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                            ['route' => 'affiliate.leads', 'label' => 'عملائي', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                            ['route' => 'affiliate.wallet', 'label' => 'محفظتي', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                            ['route' => 'affiliate.team', 'label' => 'فريقي', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                            ['route' => 'affiliate.referral-links', 'label' => 'روابطي', 'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1']
                            ] as $link)
                            <a href="{{ route($link['route']) }}"
                                class="relative px-4 py-2 text-sm font-bold transition-all duration-300 rounded-xl flex items-center gap-2
                          {{ request()->routeIs($link['route']) ? 'text-primary-700 bg-primary-50' : 'text-primary-500 hover:text-primary-700 hover:bg-primary-50/50' }}">
                                <svg class="w-4 h-4 {{ request()->routeIs($link['route']) ? 'text-primary-600' : 'opacity-50' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"></path>
                                </svg>
                                {{ $link['label'] }}
                                @if(request()->routeIs($link['route']))
                                <span class="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 bg-primary-600 rounded-full"></span>
                                @endif
                            </a>
                            @endforeach
                        </nav>

                        <div class="h-8 w-px bg-primary-100 mx-2"></div>

                        <!-- User Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-3 p-1.5 rounded-2xl hover:bg-primary-50 transition-all duration-300 group">
                                <div class="relative">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()->name) }}&background=10B981&color=FFFFFF&bold=true"
                                        class="w-10 h-10 rounded-xl border-2 border-primary-100 group-hover:border-primary-300 transition-colors">
                                    <div class="absolute -bottom-0.5 -right-0.5 w-3.5 h-3.5 bg-primary-500 border-2 border-white rounded-full"></div>
                                </div>
                                <div class="text-right hidden xl:block">
                                    <span class="block text-sm font-black text-primary-900 leading-none mb-1">{{ auth()->user()->name }}</span>
                                    <span class="block text-[10px] font-bold text-primary-400 uppercase tracking-widest">{{ auth()->user()->rank_label ?? 'مسوق' }}</span>
                                </div>
                            </button>

                            <div x-show="open" @click.away="open = false"
                                x-transition:enter="transition ease-out duration-200"
                                x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                class="absolute left-0 mt-3 w-64 bg-white rounded-3xl shadow-2xl border border-primary-50 overflow-hidden z-[100]"
                                style="display: none;">

                                <div class="p-5 bg-gradient-to-br from-primary-50 to-white border-b border-primary-50">
                                    <div class="flex items-center gap-3">
                                        <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center shadow-sm border border-primary-100">
                                            <span class="font-black text-lg text-primary-600">ن</span>
                                        </div>
                                        <div class="overflow-hidden">
                                            <p class="text-[9px] font-bold text-primary-400 uppercase tracking-wider mb-1.5">حساب العضو</p>
                                            <div class="ml-auto text-right">
                                                <div class="font-bold text-primary-900 leading-tight">{{ auth()->user()->name }}</div>
                                                <div class="font-semibold text-[10px] text-primary-400 font-mono tracking-tighter">{{ auth()->user()->email }}</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="py-2">
                                    <a href="{{ route('profile') }}" class="flex items-center px-5 py-3 text-sm font-bold text-primary-700 hover:bg-primary-50 transition-colors gap-3">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        ملفي الشخصي
                                    </a>
                                </div>

                                <div class="border-t border-gray-100 py-2">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-5 py-3 text-sm font-bold text-rose-500 hover:bg-rose-50 transition-colors gap-3">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            تسجيل الخروج
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- Hamburger -->
                            <div class="-me-2 flex items-center sm:hidden">
                                <button @click="open = ! open" class="w-11 h-11 flex items-center justify-center rounded-xl text-primary-500 hover:bg-primary-50 focus:outline-none transition-all duration-300">
                                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Mobile Navigation -->
                    <div :class="{'block': open, 'hidden': ! open}" class="hidden lg:hidden animate-fade-in-down border-t border-primary-50 bg-white">
                        <div class="pt-2 pb-6 space-y-1">
                            <a href="{{ route('dashboard') }}"
                                class="{{ request()->routeIs('dashboard') ? 'flex items-center px-5 py-3.5 rounded-xl font-bold bg-gradient-to-r from-primary-100 to-primary-50 text-primary-700 border border-primary-200' : 'flex items-center px-5 py-3.5 rounded-xl font-semibold text-primary-600 hover:bg-primary-50' }}">
                                الرئيسية
                            </a>
                            <a href="{{ route('affiliate.leads') }}"
                                class="{{ request()->routeIs('affiliate.leads') ? 'flex items-center px-5 py-3.5 rounded-xl font-bold bg-gradient-to-r from-primary-100 to-primary-50 text-primary-700 border border-primary-200' : 'flex items-center px-5 py-3.5 rounded-xl font-semibold text-primary-600 hover:bg-primary-50' }}">
                                عملائي
                            </a>
                            <a href="{{ route('affiliate.wallet') }}"
                                class="{{ request()->routeIs('affiliate.wallet') ? 'flex items-center px-5 py-3.5 rounded-xl font-bold bg-gradient-to-r from-primary-100 to-primary-50 text-primary-700 border border-primary-200' : 'flex items-center px-5 py-3.5 rounded-xl font-semibold text-primary-600 hover:bg-primary-50' }}">
                                محفظتي
                            </a>
                            <a href="{{ route('affiliate.team') }}"
                                class="{{ request()->routeIs('affiliate.team') ? 'flex items-center px-5 py-3.5 rounded-xl font-bold bg-gradient-to-r from-primary-100 to-primary-50 text-primary-700 border border-primary-200' : 'flex items-center px-5 py-3.5 rounded-xl font-semibold text-primary-600 hover:bg-primary-50' }}">
                                فريقي
                            </a>
                            <a href="{{ route('affiliate.referral-links') }}"
                                class="{{ request()->routeIs('affiliate.referral-links') ? 'flex items-center px-5 py-3.5 rounded-xl font-bold bg-gradient-to-r from-primary-100 to-primary-50 text-primary-700 border border-primary-200' : 'flex items-center px-5 py-3.5 rounded-xl font-semibold text-primary-600 hover:bg-primary-50' }}">
                                روابطي
                            </a>
                        </div>
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div>
                        <div class="font-bold text-primary-900 text-base leading-tight">{{ auth()->user()->name }}</div>
                        <div class="font-semibold text-[10px] text-primary-400 mt-0.5 uppercase tracking-wider">{{ auth()->user()->email }}</div>
                    </div>
                </div>

                <div class="space-y-1">
                    <a href="{{ route('profile') }}" wire:navigate class="flex items-center px-5 py-3 font-semibold text-primary-700 hover:bg-primary-50 rounded-xl">
                        {{ __('الملف الشخصي') }}
                    </a>

                    <button wire:click="logout" class="w-full text-start">
                        <div class="flex items-center px-5 py-3 font-bold text-rose-500 hover:bg-rose-50 rounded-xl">
                            {{ __('تسجيل الخروج') }}
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>