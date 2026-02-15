<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;
use App\Models\Setting;

new #[Layout('layouts.admin')] class extends Component {
    use WithFileUploads;

    public $available_systems = [];
    public $search = '';

    // Form State
    public $showModal = false;
    public $isEditMode = false;
    public $editingIndex = null;

    // Form fields
    public $new_system_name = '';
    public $new_system_id = '';
    public $new_system_image = null;

    public function mount()
    {
        $this->loadSystems();
    }

    public function loadSystems()
    {
        $this->available_systems = json_decode(Setting::get('available_systems', '[]'), true);
        if (empty($this->available_systems)) {
            $this->available_systems = [
                ['name' => 'قيود', 'id' => 'qoyod'],
                ['name' => 'دفترة', 'id' => 'daftra'],
            ];
        }
    }

    public function openCreateModal()
    {
        $this->reset(['new_system_name', 'new_system_id', 'new_system_image', 'editingIndex', 'isEditMode']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function editSystem($index)
    {
        $this->resetValidation();
        $this->isEditMode = true;
        $this->editingIndex = $index;
        $this->new_system_name = $this->available_systems[$index]['name'];
        $this->new_system_id = $this->available_systems[$index]['id'];
        $this->new_system_image = null;
        $this->showModal = true;
    }

    public function save()
    {
        if ($this->isEditMode) {
            $this->updateSystem();
        } else {
            $this->addSystem();
        }
    }

    public function addSystem()
    {
        $this->validate([
            'new_system_name' => 'required|string|max:50',
            'new_system_id' => 'required|string|max:20|alpha_dash',
            'new_system_image' => 'nullable|image|max:1024',
        ]);

        foreach ($this->available_systems as $sys) {
            if ($sys['id'] === $this->new_system_id) {
                $this->addError('new_system_id', 'هذا المعرف موجود بالفعل');
                return;
            }
        }

        if ($this->new_system_image) {
            $this->new_system_image->storeAs('images/systems', $this->new_system_id . '.png', 'public_uploads');
        }

        $this->available_systems[] = [
            'name' => $this->new_system_name,
            'id' => $this->new_system_id
        ];

        $this->persist();
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'تم إضافة الخدمة بنجاح');
    }

    public function updateSystem()
    {
        $this->validate([
            'new_system_name' => 'required|string|max:50',
            'new_system_id' => 'required|string|max:20|alpha_dash',
            'new_system_image' => 'nullable|image|max:1024',
        ]);

        if ($this->new_system_image) {
            $this->new_system_image->storeAs('images/systems', $this->new_system_id . '.png', 'public_uploads');
        }

        $this->available_systems[$this->editingIndex] = [
            'name' => $this->new_system_name,
            'id' => $this->new_system_id
        ];

        $this->persist();
        $this->showModal = false;
        $this->dispatch('toast', type: 'success', message: 'تم تحديث الخدمة بنجاح');
    }

    public function removeSystem($index)
    {
        unset($this->available_systems[$index]);
        $this->available_systems = array_values($this->available_systems);
        $this->persist();
        $this->dispatch('toast', type: 'success', message: 'تم حذف الخدمة بنجاح');
    }

    private function persist()
    {
        Setting::set('available_systems', json_encode($this->available_systems));
    }

    public function with()
    {
        $filtered = $this->available_systems;
        if ($this->search) {
            $filtered = array_filter($filtered, function ($system) {
                return str_contains($system['name'], $this->search) ||
                    str_contains(strtolower($system['id']), strtolower($this->search));
            });
        }

        return [
            'systems' => $filtered
        ];
    }
}; ?>

<div class="space-y-8 pb-12" x-data="{ show: @entangle('showModal') }">
    <!-- Header Card -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center text-blue-600 shadow-sm border border-blue-100">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a2 2 0 00-1.96 1.414l-.477 2.387a2 2 0 00.547 1.022l1.414 1.414a2 2 0 001.022.547l2.387.477a2 2 0 001.96-1.414l.477-2.387a2 2 0 00-.547-1.022l-1.414-1.414z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12l-4-4m0 0l-4 4m4-4v12" />
                </svg>
            </div>
            <div>
                <h2 class="text-3xl font-black text-gray-900 leading-tight">إدارة الخدمات</h2>
                <p class="text-gray-500 font-bold mt-1">عرض وتعديل الخدمات والأنظمة المتكاملة في النظام</p>
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
                        <span>إضافة خدمة جديدة</span>
                    </button>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <!-- List Table -->
        <div class="overflow-x-auto mt-6">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-primary-400 text-[11px] font-black uppercase tracking-widest border-b border-gray-50">
                        <th class="pb-4 text-right px-4">الشعار والخدمة</th>
                        <th class="pb-4 text-right px-4">المعرف (ID)</th>
                        <th class="pb-4 text-center px-4">العمليات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($systems as $index => $system)
                    <tr wire:key="system-row-{{ $system['id'] }}" class="group hover:bg-gray-50/50 transition-colors duration-200">
                        <td class="py-5 px-4">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-gray-100 p-2 flex items-center justify-center transition-transform group-hover:scale-105">
                                    <img src="{{ asset('images/systems/'.$system['id'].'.png') }}" class="w-full h-full object-contain" alt="{{ $system['name'] }}" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($system['name']) }}&background=f1f5f9&color=64748b&bold=true'">
                                </div>
                                <span class="font-black text-gray-900 text-lg tracking-tight">{{ $system['name'] }}</span>
                            </div>
                        </td>
                        <td class="py-5 px-4">
                            <span class="font-mono text-xs font-black text-blue-600 bg-blue-50 px-3 py-1.5 rounded-xl border border-blue-100/50 uppercase tracking-wider">{{ $system['id'] }}</span>
                        </td>
                        <td class="py-5 px-4">
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" wire:click="editSystem({{ $index }})" class="w-10 h-10 flex items-center justify-center text-blue-600 bg-blue-50 hover:bg-blue-600 hover:text-white rounded-xl transition-all shadow-sm border border-blue-100" title="تعديل">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button type="button" wire:click="removeSystem({{ $index }})" wire:confirm="هل أنت متأكد من حذف هذه الخدمة؟" class="w-10 h-10 flex items-center justify-center text-rose-500 bg-rose-50 hover:bg-rose-600 hover:text-white rounded-xl transition-all shadow-sm border border-rose-100" title="حذف">
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
                            <p class="text-gray-400 font-bold italic">لا توجد خدمات مطابقة للبحث</p>
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
                    <h3 class="text-xl font-black text-gray-900">{{ $isEditMode ? 'تعديل الخدمة' : 'إضافة خدمة جديدة' }}</h3>
                    <p class="text-gray-500 text-xs font-bold mt-1">أدخل بيانات الخدمة التقنية المراد إدارتها</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 p-2 hover:bg-white rounded-xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form wire:submit.prevent="save" class="p-8 space-y-6">
                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 mr-1">اسم الخدمة <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="new_system_name" class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold" placeholder="مثال: قيود للمحاسبة">
                    @error('new_system_name') <span class="text-rose-500 text-xs font-bold mr-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-bold text-gray-700 mr-1">المعرف التقني (ID) <span class="text-rose-500">*</span></label>
                    <input type="text" wire:model="new_system_id" class="w-full px-4 py-3.5 bg-gray-50 border border-gray-200 rounded-2xl focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500 transition-all font-bold" placeholder="qoyod" dir="ltr" {{ $isEditMode ? 'readonly opacity-60 cursor-not-allowed' : '' }}>
                    @error('new_system_id') <span class="text-rose-500 text-xs font-bold mr-1">{{ $message }}</span> @enderror
                </div>

                <div class="space-y-3">
                    <label class="block text-sm font-bold text-gray-700 mr-1">شعار الخدمة (PNG)</label>
                    <div class="bg-gray-50 border-2 border-dashed border-gray-200 rounded-2xl p-6 flex flex-col items-center gap-4 transition-colors hover:border-blue-200 relative group">
                        <input type="file" wire:model="new_system_image" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="w-20 h-20 bg-white rounded-2xl shadow-md border border-gray-100 flex items-center justify-center overflow-hidden">
                            @if($new_system_image)
                            <img src="{{ $new_system_image->temporaryUrl() }}" class="w-full h-full object-contain">
                            @elseif($isEditMode)
                            <img src="{{ asset('images/systems/'.$new_system_id.'.png') }}" class="w-full h-full object-contain" onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($new_system_name) }}&background=f1f5f9&color=64748b&bold=true'">
                            @else
                            <svg class="w-10 h-10 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            @endif
                        </div>
                        <span class="text-xs font-black text-blue-600">اضغط لتغيير الصورة</span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-gray-50">
                    <button type="button" @click="show = false" class="px-8 py-3.5 rounded-2xl border border-gray-200 text-gray-600 font-bold hover:bg-gray-50 transition-all">إلغاء</button>
                    <button type="submit" class="btn btn-primary px-10">
                        {{ $isEditMode ? 'حفظ التغييرات' : 'إضافة الخدمة' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>