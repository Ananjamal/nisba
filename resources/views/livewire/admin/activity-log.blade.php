<div class="flex flex-col h-full max-h-[90vh]">
    {{-- Header --}}
    <div class="px-8 py-6 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-primary-50/50 to-white">
        <div>
            <h3 class="text-xl font-black text-gray-900">سجل نشاطات المستخدم</h3>
            <p class="text-gray-500 text-xs font-bold mt-1">عرض آخر 20 نشاط متعلق بـ {{ $user->name }}</p>
        </div>
        <button wire:click="$dispatch('close-modal')" class="p-2.5 rounded-xl hover:bg-gray-100 transition-all text-gray-400 hover:text-gray-600">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    {{-- Body --}}
    <div class="p-8 overflow-y-auto">
        <div class="space-y-6">
            @forelse($activities as $activity)
            <div class="flex gap-4 group">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 rounded-xl bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 transition-colors group-hover:bg-primary-600 group-hover:text-white shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="w-px h-full bg-primary-50 group-last:hidden mt-2"></div>
                </div>
                <div class="flex-1 pb-8">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-black text-primary-600 uppercase tracking-widest bg-primary-50 px-2 py-0.5 rounded-md">
                            {{ $activity->getTypeLabel() }}
                        </span>
                        <span class="text-[10px] font-bold text-gray-400 uppercase">
                            {{ $activity->created_at->diffForHumans() }}
                        </span>
                    </div>
                    <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100 group-hover:border-primary-100 group-hover:bg-white transition-all shadow-sm/0 group-hover:shadow-sm">
                        <p class="text-sm font-bold text-gray-700 leading-relaxed">
                            {{ $activity->getFormattedDescription() }}
                        </p>

                        <div class="mt-3 pt-3 border-t border-gray-100 flex items-center justify-between text-[10px] text-gray-400 font-bold">
                            <div class="flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span>المسؤول: {{ $activity->causer?->name ?? 'النظام' }}</span>
                            </div>
                            @if($activity->ip_address)
                            <div class="flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                </svg>
                                <span>IP: {{ $activity->ip_address }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @empty
            <div class="text-center py-12 bg-gray-50 rounded-3xl border-2 border-dashed border-gray-200">
                <div class="w-16 h-16 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center mx-auto mb-4 text-gray-300">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <p class="text-sm text-gray-400 font-bold italic">لا توجد نشاطات مسجلة لهذا المستخدم</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Footer --}}
    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 flex justify-end">
        <button wire:click="$dispatch('close-modal')" class="px-6 py-2.5 bg-white border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
            إغلاق
        </button>
    </div>
</div>