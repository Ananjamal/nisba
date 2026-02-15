<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use App\Models\Setting;

new #[Layout('layouts.admin')] class extends Component {
    public $available_sectors = [];
    public $search = '';

    // Modal state
    public $showModal = false;
    public $isEditMode = false;
    public $editingIndex = null;
    public $new_sector_name = '';

    public function mount()
    {
        $this->loadSectors();
    }

    public function loadSectors()
    {
        $this->available_sectors = json_decode(Setting::get('available_sectors', '[]'), true);
        if (empty($this->available_sectors)) {
            $this->available_sectors = ['العقارات', 'التقنية والبرمجة', 'التسويق والدعاية', 'التجارة الإلكترونية', 'التعليم', 'الصحة', 'الخدمات المالية', 'المقاولات والبناء', 'المطاعم والكافيهات'];
        }
    }

    public function openCreateModal()
    {
        $this->reset(['new_sector_name', 'editingIndex', 'isEditMode']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function editSector($index)
    {
        $this->resetValidation();
        $this->isEditMode = true;
        $this->editingIndex = $index;
        $this->new_sector_name = $this->available_sectors[$index];
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->isEditMode) {
            $this->updateSector();
        } else {
            $this->addSector();
        }
    }

    public function addSector()
    {
        $this->validate([
            'new_sector_name' => 'required|string|max:50',
        ]);

        if (in_array($this->new_sector_name, $this->available_sectors)) {
            $this->addError('new_sector_name', 'هذا القطاع موجود بالفعل');
            return;
        }

        $this->available_sectors[] = $this->new_sector_name;
        $this->persist();
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'تم إضافة القطاع بنجاح');
    }

    public function updateSector()
    {
        $this->validate([
            'new_sector_name' => 'required|string|max:50',
        ]);

        foreach ($this->available_sectors as $idx => $name) {
            if ($name === $this->new_sector_name && $idx !== $this->editingIndex) {
                $this->addError('new_sector_name', 'اسم القطاع موجود بالفعل');
                return;
            }
        }

        $this->available_sectors[$this->editingIndex] = $this->new_sector_name;
        $this->persist();
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'تم تحديث القطاع بنجاح');
    }

    public function removeSector($index)
    {
        unset($this->available_sectors[$index]);
        $this->available_sectors = array_values($this->available_sectors);
        $this->persist();
        $this->dispatch('toast', type: 'success', message: 'تم حذف القطاع بنجاح');
    }

    private function persist()
    {
        Setting::set('available_sectors', json_encode($this->available_sectors));
    }

    public function with()
    {
        $filtered = $this->available_sectors;
        if ($this->search) {
            $filtered = array_filter($filtered, function ($name) {
                return str_contains($name, $this->search);
            });
        }

        return [
            'sectors' => $filtered
        ];
    }
}; ?>

<div class="space-y-8 pb-12" x-data="{ show: @entangle('showModal') }">
    <!-- Header Card -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <div class="w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center text-emerald-600 shadow-sm border border-emerald-100">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 leading-tight">إدارة القطاعات</h2>
                <p class="text-gray-500 font-bold mt-1">تصنيف وترتيب مجالات عمل العملاء والشركات</p>
            </div>
        </div>
    </div>

    <!-- Filter Bar (Staff Style) -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <x-table.filter-bar :statusOptions="[]" :showDate="false">
            <x-slot name="actions">
                <div class="flex gap-2">
                    <button wire:click="openCreateModal" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>إضافة قطاع جديد</span>
                    </button>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <!-- List Table -->
        <div class="overflow-x-auto mt-6">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-primary-400 text-[11px] font-black uppercase tracking-widest border-b border-gray-50">
                        <th class="pb-4 text-right px-4">اسم القطاع</th>
                        <th class="pb-4 text-center px-4">العمليات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($sectors as $index => $sector)
                    <tr wire:key="sector-row-{{ $index }}" class="group hover:bg-gray-50/50 transition-colors duration-200">
                        <td class="py-5 px-4">
                            <div class="flex items-center gap-4">
                                <div class="w-1.5 h-8 bg-emerald-100 group-hover:bg-emerald-500 rounded-full transition-colors"></div>
                                <span class="font-black text-gray-900 text-lg tracking-tight">{{ $sector }}</span>
                            </div>
                        </td>
                        <td class="py-5 px-4">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" wire:click="editSector({{ $index }})" class="w-10 h-10 flex items-center justify-center text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm border border-blue-100" title="تعديل">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button type="button" wire:click="removeSector({{ $index }})" wire:confirm="هل أنت متأكد من حذف هذا القطاع كلياً؟" class="w-10 h-10 flex items-center justify-center text-rose-500 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all shadow-sm border border-rose-100" title="حذف">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-16 text-center">
                            <p class="text-gray-400 font-bold italic">لا توجد قطاعات مطابقة للبحث</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal (Staff Style) -->
    <div x-show="show"
        x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-[100] flex items-start justify-center p-4 overflow-y-auto"
        style="display: none;">

        <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
            @click="show = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"></div>

        <div class="relative bg-white rounded-3xl w-full max-w-lg shadow-2xl mt-12 mb-6 flex flex-col border border-gray-100"
            @click.away="show = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

            <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50 rounded-t-3xl">
                <div>
                    <h3 class="text-xl font-black text-gray-900">{{ $isEditMode ? 'تعديل القطاع' : 'إضافة قطاع جديد' }}</h3>
                    <p class="text-gray-500 text-xs font-bold mt-1">أدخل اسم القطاع المراد إضافته للتوصيات</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 p-2 hover:bg-white rounded-xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="save" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 mr-1">اسم القطاع <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="new_sector_name" class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-emerald-500/10 focus:border-emerald-500 transition-all font-bold" placeholder="مثال: الخدمات اللوجستية والشحن">
                    @error('new_sector_name') <span class="text-rose-500 text-xs font-bold mr-1">{{ $message }}</span> @enderror
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                    <button type="button" @click="show = false" class="px-8 py-3.5 rounded-2xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-10">
                        {{ $isEditMode ? 'حفظ التغييرات' : 'إضافة القطاع' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>