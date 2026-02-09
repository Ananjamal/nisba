@props(['columns', 'labels'])

<div class="relative" x-data="{ open: false }">
    <button @click="open = !open"
        class="group flex items-center gap-2 px-4 py-4 bg-white border border-gray-100 rounded-xl hover:border-primary-300 hover:bg-primary-50 transition-all duration-300 shadow-sm"
        title="إظهار/إخفاء الأعمدة">
        <svg class="w-4 h-4 text-primary-500 group-hover:text-primary-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
        <span class="text-xs font-bold text-primary-900">الأعمدة</span>
    </button>

    <div x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-2"
        @click.away="open = false"
        class="absolute top-full left-0 md:right-0 md:left-auto mt-2 w-64 bg-white rounded-2xl shadow-2xl border border-primary-50 z-[100] p-4 overflow-hidden"
        style="display: none;">

        <div class="flex items-center justify-between mb-4 pb-2 border-b border-gray-50">
            <span class="text-xs font-black text-primary-900 uppercase tracking-wider">إدارة الأعمدة</span>
            <button @click="open = false" class="text-gray-400 hover:text-primary-600 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div class="space-y-1.5 max-h-[300px] overflow-y-auto custom-scrollbar pr-1">
            @foreach($columns as $key => $visible)
            <label class="group flex items-center justify-between p-2.5 hover:bg-primary-50 rounded-xl cursor-pointer transition-all duration-200">
                <div class="flex items-center gap-3">
                    <div class="relative flex items-center justify-center w-5 h-5 rounded-md border-2 transition-all duration-200 {{ $visible ? 'bg-primary-600 border-primary-600' : 'bg-white border-gray-300 group-hover:border-primary-400' }}">
                        <input type="checkbox"
                            wire:click="toggleColumn('{{ $key }}')"
                            @if($visible) checked @endif
                            class="absolute opacity-0 w-full h-full cursor-pointer z-10">
                        @if($visible)
                        <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                        </svg>
                        @endif
                    </div>
                    <span class="text-sm font-bold {{ $visible ? 'text-primary-900' : 'text-gray-500' }} transition-colors">{{ $labels[$key] ?? $key }}</span>
                </div>

                @if($visible)
                <div class="w-1.5 h-1.5 rounded-full bg-primary-500 shadow-[0_0_8px_rgba(34,197,94,0.5)]"></div>
                @endif
            </label>
            @endforeach
        </div>
    </div>
</div>