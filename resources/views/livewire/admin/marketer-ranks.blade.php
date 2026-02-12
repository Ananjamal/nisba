<div class="space-y-6">
    <div class="flex justify-between items-center bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">إدارة رتب المسوقين</h2>
            <p class="text-gray-500 mt-1">تحكم في رتب المسوقين ومضاعفات العمولة الخاصة بهم</p>
        </div>
        <div class="flex items-center gap-4">
            @foreach($allRanks as $rank)
            <div class="{{ str_replace('bg-', 'bg-opacity-50 bg-', $rank->color) }} p-3 rounded-2xl border flex items-center gap-3">
                <span class="text-2xl">{{ $rank->icon }}</span>
                <span class="text-sm font-bold">{{ $rank->getLabelAttribute() ?? $rank->name }}: {{ number_format($rank->commission_multiplier, 1) }}x</span>
            </div>
            @endforeach
        </div>
    </div>

    @if (session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row gap-4 mb-6">
            <div class="flex-1 relative group">
                <input type="text" wire:model.live="search" placeholder="بحث بالاسم أو البريد..." class="w-full pl-4 pr-10 py-3 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-bold text-gray-900 placeholder:text-gray-400 hover:border-gray-300 shadow-sm">
                <div class="absolute inset-y-0 right-3 flex items-center pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 group-focus-within:text-blue-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
            <div class="relative min-w-[200px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none">
                    <span class="text-xl">🎖️</span>
                </div>
                <select wire:model.live="rank_filter" class="w-full appearance-none pl-4 pr-10 py-3 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-sm transition-all font-bold text-gray-700 hover:border-gray-300">
                    <option value="">جميع الرتب</option>
                    @foreach($allRanks as $rank)
                    <option value="{{ $rank->name }}">{{ $rank->label }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right border-collapse">
                <thead>
                    <tr class="text-gray-500 text-sm border-b border-gray-100">
                        <th class="pb-4 font-semibold text-start">المسوق</th>
                        <th class="pb-4 font-semibold text-start">الرتبة الحالية</th>
                        <th class="pb-4 font-semibold text-start">تغيير الرتبة</th>
                        <th class="pb-4 font-semibold text-start">مضاعف العمولة</th>
                        <th class="pb-4 font-semibold text-start">الإجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @foreach($marketers as $marketer)
                    <tr class="group hover:bg-gray-50 transition-all duration-300 border-b border-gray-50 last:border-0">
                        <td class="py-5">
                            <div class="font-bold text-gray-900">{{ $marketer->name }}</div>
                            <div class="text-xs text-gray-400 font-bold">{{ $marketer->email }}</div>
                        </td>
                        <td class="py-5">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold border shadow-sm {{ $marketer->getRankBadgeColor() }}">
                                {{ $marketer->getRankIcon() }} {{ $marketer->getRankLabel() }}
                            </span>
                        </td>
                        <td class="py-5">
                            <div class="flex gap-2">
                                @foreach($allRanks as $rank)
                                <button wire:click="updateRank({{ $marketer->id }}, '{{ $rank->name }}')"
                                    class="p-1.5 px-3 rounded-xl text-xs font-bold transition-all shadow-sm transform hover:-translate-y-0.5 
                                    {{ $marketer->rank === $rank->name ? str_replace('bg-', 'bg-opacity-100 bg-', $rank->color) . ' text-white' : str_replace(['text-', 'bg-'], ['text-opacity-80 text-', 'bg-opacity-50 bg-'], $rank->color) }}">
                                    {{ $marketer->getRankLabel($rank->name) }}
                                </button>
                                @endforeach
                            </div>
                        </td>
                        <td class="py-5">
                            <div class="flex items-center gap-2">
                                <input type="number" step="0.01"
                                    class="w-24 px-3 py-1.5 text-sm font-bold text-center border-gray-200 rounded-xl focus:ring-blue-500 focus:border-blue-500 transition-all"
                                    value="{{ $marketer->commission_multiplier }}"
                                    placeholder="المعامل"
                                    onchange="@this.updateMultiplier({{ $marketer->id }}, this.value)">
                                <span class="text-xs font-bold text-gray-400">x</span>
                            </div>
                        </td>
                        <td class="py-5 text-left">
                            <a href="{{ route('admin.affiliates.show', $marketer->id) }}" title="عرض التفاصيل">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $marketers->links() }}
        </div>
    </div>
</div>