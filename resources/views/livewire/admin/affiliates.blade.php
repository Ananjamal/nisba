<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;
    use App\Livewire\Traits\WithDynamicTable;

    public $userId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $selectedRoles = ['affiliate'];
    public $sector = '';
    public $sector_filter = '';
    public $showModal = false;

    public function mount()
    {
        $this->loadTablePrefs([
            'marketer' => true,
            'phone' => true,
            'rank' => true,
            'sector' => true,
            'email' => true,
            'joined_at' => true,
            'status' => true,
            'actions' => true,
        ]);
    }

    public $showViewModal = false;
    public $viewUser = null;

    public function openViewModal($id)
    {
        $this->viewUser = User::withCount('leads')->findOrFail($id);
        $this->showViewModal = true;
    }

    public function with()
    {
        return [
            'users' => User::where('role', 'affiliate')
                ->withCount('leads')
                ->when($this->search, function ($query) {
                    $query->where(function ($q) {
                        $q->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
                })
                ->when($this->sector_filter, function ($query) {
                    $query->where('sector', $this->sector_filter);
                })
                ->orderBy($this->sortField, $this->sortDirection)
                ->paginate(10),
        ];
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->sector = '';
        $this->selectedRoles = ['affiliate'];
        $this->resetValidation();
    }

    public function createUser()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function editUser($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->sector = $user->sector;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        if (empty($this->selectedRoles)) $this->selectedRoles = ['affiliate'];
        $this->password = ''; // Don't show password
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveUser()
    {
        $this->validate([
            'name' => 'required|min:3',
            'email' => 'required|email|unique:users,email,' . ($this->userId ?: 'NULL'),
            'password' => $this->userId ? 'nullable|min:8' : 'required|min:8',
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => 'exists:roles,name',
            'sector' => 'nullable|string|max:255',
        ]);

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'sector' => $this->sector,
            ]);
            if ($this->password) {
                $user->update(['password' => bcrypt($this->password)]);
            }
            $user->syncRoles($this->selectedRoles);
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'sector' => $this->sector,
                'password' => bcrypt($this->password),
            ]);
            $user->assignRole($this->selectedRoles);
        }

        $this->showModal = false;
        $this->resetForm();
        session()->flash('message', 'تم حفظ بيانات المستخدم بنجاح.');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('message', 'تم حذف المستخدم بنجاح.');
    }
}; ?>

<div class="space-y-8" x-data="{ showModal: @entangle('showModal'), showDeleteModal: false, deletingId: null }">
    @if (session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif

    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <x-table.filter-bar :statusOptions="[]">
            <x-slot name="actions">
                <div class="flex gap-2">
                    <x-table.column-toggler :columns="$columns" :labels="[
                    'marketer' => 'المسوق',
                    'phone' => 'رقم الهاتف',
                    'rank' => 'المستوى',
                    'sector' => 'القطاع',
                    'email' => 'البريد الإلكتروني',
                    'joined_at' => 'تاريخ التسجيل',
                    'status' => 'عدد العملاء',
                    'actions' => 'العمليات'
                ]" />

                    <a href="{{ route('admin.reports.affiliates.excel', ['search' => $search, 'sector' => $sector_filter]) }}"
                        target="_blank"
                        class="group flex items-center justify-center p-2.5 bg-white border border-gray-100 rounded-xl hover:border-green-500 hover:bg-green-50 transition-all duration-300 shadow-sm"
                        title="تصدير Excel">
                        <svg class="w-5 h-5 text-green-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </a>
                    <a href="{{ route('admin.reports.affiliates.pdf', ['search' => $search, 'sector' => $sector_filter]) }}"
                        target="_blank"
                        class="group flex items-center justify-center p-2.5 bg-white border border-gray-100 rounded-xl hover:border-red-500 hover:bg-red-50 transition-all duration-300 shadow-sm"
                        title="تصدير PDF">
                        <svg class="w-5 h-5 text-red-600 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z M12 11h4m-4 4h4m-4-8h4" />
                        </svg>
                    </a>

                    <button wire:click="createUser" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>إضافة مسوق</span>
                    </button>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <!-- فلاتر إضافية -->
        <div class="flex flex-wrap items-center gap-3 mb-6">
            <div class="relative w-full md:w-auto min-w-[180px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none z-10">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <select wire:model.live="sector_filter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-white border border-gray-100 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer shadow-sm transition-all text-sm font-bold text-gray-700 hover:border-gray-300">
                    <option value="">جميع القطاعات</option>
                    @foreach(['العقارات', 'التقنية والبرمجة', 'التسويق والدعاية', 'التجارة الإلكترونية', 'التعليم', 'الصحة', 'الخدمات المالية', 'المقاولات والبناء', 'المطاعم والكافيهات', 'أخرى'] as $sec)
                    <option value="{{ $sec }}">{{ $sec }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 left-3 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>

            @if($sector_filter)
            <button wire:click="$set('sector_filter', '')"
                class="px-4 py-2.5 text-sm font-bold text-gray-600 bg-gray-50 hover:bg-gray-100 border border-gray-200 rounded-xl transition-all shadow-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                </svg>
                إعادة تعيين
            </button>
            @endif
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-primary-400 text-sm border-b border-primary-50">
                        @if($columns['marketer'])
                        <x-table.th field="name" :sortField="$sortField" :sortDirection="$sortDirection" label="المسوق" />
                        @endif
                        @if($columns['phone'])
                        <x-table.th field="phone" :sortField="$sortField" :sortDirection="$sortDirection" label="رقم الهاتف" />
                        @endif
                        @if($columns['rank'])
                        <x-table.th field="rank" :sortField="$sortField" :sortDirection="$sortDirection" label="المستوى" />
                        @endif
                        @if($columns['sector'])
                        <x-table.th field="sector" :sortField="$sortField" :sortDirection="$sortDirection" label="القطاع" />
                        @endif
                        @if($columns['email'])
                        <x-table.th field="email" :sortField="$sortField" :sortDirection="$sortDirection" label="البريد الإلكتروني" />
                        @endif
                        @if($columns['joined_at'])
                        <x-table.th field="created_at" :sortField="$sortField" :sortDirection="$sortDirection" label="تاريخ التسجيل" />
                        @endif
                        @if($columns['status'])
                        <x-table.th field="leads_count" :sortField="$sortField" :sortDirection="$sortDirection" label="عدد العملاء" />
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-bold text-left">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @foreach($users as $user)
                    <tr class="group hover:bg-gray-50 transition-colors duration-200">
                        @if($columns['marketer'])
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary-600 font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <button wire:click="openViewModal({{ $user->id }})" class="font-bold text-gray-900 block hover:text-primary-600 transition text-right">{{ $user->name }}</button>
                            </div>
                        </td>
                        @endif
                        @if($columns['phone'])
                        <td class="py-4 font-bold text-gray-600">{{ $user->phone ?? '-' }}</td>
                        @endif
                        @if($columns['rank'])
                        <td class="py-4">
                            <span class="px-2 py-1 rounded-lg text-xs font-bold border {{ $user->getRankBadgeColor() }}">
                                {{ $user->getRankIcon() }} {{ $user->getRankLabel() }}
                            </span>
                        </td>
                        @endif
                        @if($columns['sector'])
                        <td class="py-4 font-bold text-gray-600">{{ $user->sector ?? '-' }}</td>
                        @endif
                        @if($columns['email'])
                        <td class="py-4 font-bold text-primary-900">{{ $user->email }}</td>
                        @endif
                        @if($columns['joined_at'])
                        <td class="py-4 font-bold text-primary-600">{{ $user->created_at->format('Y-m-d') }}</td>
                        @endif
                        @if($columns['status'])
                        <td class="py-4">
                            <span class="px-3 py-1 rounded-full text-[10px] font-black bg-primary-50 text-primary-600 border border-primary-100">
                                {{ $user->leads_count }} عملاء
                            </span>
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-4">
                            <div class="flex gap-2">
                                <button wire:click="openViewModal({{ $user->id }})" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="عرض التفاصيل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </button>
                                <button wire:click="editUser({{ $user->id }})" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:click="$set('deletingId', {{ $user->id }}); $set('showDeleteModal', true)" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all duration-300" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Create/Edit User Modal -->
    <div x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-on:keydown.escape.window="show = false"
        class="fixed inset-0 z-[100] flex items-start justify-center p-4 overflow-y-auto"
        style="display: none;">
        <!-- Backdrop -->
        <div class="fixed inset-0 bg-gray-900/75 backdrop-blur-sm transition-opacity"
            @click="show = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"></div>

        <!-- Modal Container -->
        <div class="relative bg-white rounded-2xl w-full max-w-lg shadow-2xl mt-4 mb-6 flex flex-col border-2 border-gray-200"
            @click.away="show = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

            <!-- Header - Soft and Clean -->
            <div class="bg-gradient-to-b from-gray-50 to-white px-8 py-6 flex justify-between items-center flex-shrink-0 border-b border-gray-200">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $userId ? __('تعديل بيانات المسوق') : __('إضافة مسوق جديد') }}</h3>
                    <p class="text-gray-500 text-sm mt-1.5">{{ __('أدخل معلومات المسوق للبدء') }}</p>
                </div>
                <button @click="show = false"
                    class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2.5 rounded-xl transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content -->
            <form wire:submit="saveUser" class="flex flex-col flex-1">
                <div class="p-8 space-y-6 overflow-y-auto bg-white flex-1">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">{{ __('الاسم الكامل') }}</label>
                        <input type="text" wire:model="name" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">{{ __('البريد الإلكتروني') }}</label>
                        <input type="email" wire:model="email" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white">
                        @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">{{ __('المجال / القطاع') }}</label>
                        <select wire:model="sector" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white">
                            <option value="">اختر القطاع</option>
                            <option value="العقارات">العقارات</option>
                            <option value="التقنية والبرمجة">التقنية والبرمجة</option>
                            <option value="التسويق والدعاية">التسويق والدعاية</option>
                            <option value="التجارة الإلكترونية">التجارة الإلكترونية</option>
                            <option value="التعليم">التعليم</option>
                            <option value="الصحة">الصحة</option>
                            <option value="الخدمات المالية">الخدمات المالية</option>
                            <option value="المقاولات والبناء">المقاولات والبناء</option>
                            <option value="المطاعم والكافيهات">المطاعم والكافيهات</option>
                            <option value="أخرى">أخرى</option>
                        </select>
                        @error('sector') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">{{ __('كلمة المرور') }} <span class="text-xs text-gray-400 font-normal">{{ $userId ? __('(اتركها فارغة لعدم التغيير)') : '' }}</span></label>
                        <input type="password" wire:model="password" class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white">
                        @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">{{ __('الأدوار الوظيفية') }} <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            @foreach(\Spatie\Permission\Models\Role::all() as $r)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" wire:model="selectedRoles" value="{{ $r->name }}"
                                    class="w-4 h-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition-all bg-white">
                                <span class="font-bold text-gray-700 text-sm group-hover:text-gray-900 transition-colors">
                                    {{ $r->name }}
                                </span>
                            </label>
                            @endforeach
                        </div>
                        @error('selectedRoles') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                    <button type="button" @click="show = false"
                        class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-white hover:border-gray-400 font-semibold transition-all duration-200 flex items-center gap-2">
                        إلغاء
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <span wire:loading.remove>{{ $userId ? __('حفظ التغييرات') : __('إنشاء الحساب') }}</span>
                        <span wire:loading class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            {{ __('جاري المعالجة...') }}
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>


    <!-- Professional Delete Confirmation Modal -->
    <template x-teleport="body">
        <div x-show="showDeleteModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showDeleteModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-sm transform overflow-hidden rounded-3xl bg-white p-8 shadow-2xl transition-all text-center">
                    <div class="mb-6">
                        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-red-50 text-red-600 mb-4">
                            <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-black text-primary-900 mb-2">هل أنت متأكد؟</h3>
                        <p class="text-primary-500 font-medium">سيتم حذف بيانات المسوق نهائياً، هذا الإجراء لا يمكن التراجع عنه.</p>
                    </div>

                    <div class="flex flex-col gap-3">
                        <button
                            @click="$wire.deleteUser(deletingId); showDeleteModal = false"
                            class="w-full py-4 bg-red-600 hover:bg-red-700 text-white rounded-2xl font-bold shadow-lg shadow-red-200 hover:shadow-red-300 transition-all transform active:scale-95">
                            تأكيد الحذف
                        </button>
                        <button
                            @click="showDeleteModal = false"
                            class="w-full py-4 bg-primary-50 text-primary-600 rounded-2xl font-bold hover:bg-primary-100 transition-all font-bold">
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Professional View Details Modal -->
    <template x-teleport="body">
        <div x-data="{ showViewModal: $wire.entangle('showViewModal') }"
            x-show="showViewModal"
            x-on:keydown.escape.window="showViewModal = false"
            class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="showViewModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    <!-- Modal Header -->
                    <div class="px-8 py-6 border-b border-primary-50 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                        <div>
                            <h3 class="text-2xl font-black text-primary-900">تفاصيل المسوق</h3>
                            <p class="text-primary-500 text-sm font-medium">عرض المعلومات الكاملة للمسوق والأداء</p>
                        </div>
                        <button @click="showViewModal = false" class="p-2 rounded-full hover:bg-white hover:shadow-md transition-all text-primary-400 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @if($viewUser)
                    <div class="p-8 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- User Basic Info -->
                            <div class="space-y-6">
                                <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                    <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">المعلومات الشخصية</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600 font-bold">
                                                {{ substr($viewUser->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-primary-400 font-bold">الاسم الكامل</p>
                                                <p class="font-black text-primary-900 text-lg line-height-1">{{ $viewUser->name }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-primary-400 font-bold">رقم الهاتف</p>
                                                <p class="font-black text-primary-900 text-lg leading-none">{{ $viewUser->phone ?: 'غير متوفر' }}</p>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            <div class="overflow-hidden">
                                                <p class="text-[10px] text-primary-400 font-bold">البريد الإلكتروني</p>
                                                <p class="font-black text-primary-900 text-xs truncate">{{ $viewUser->email }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">تفاصيل إضافية</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">القطاع</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->sector ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">تاريخ الانضمام</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->created_at->format('Y-m-d') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Performance & Financial -->
                            <div class="space-y-6">
                                <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                    <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">مستوى الأداء</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center justify-between p-3 bg-white rounded-2xl border border-primary-50 shadow-sm">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-2xl shadow-sm border border-gray-100">
                                                    {{ $viewUser->getRankIcon() }}
                                                </div>
                                                <div>
                                                    <p class="text-[10px] text-primary-400 font-bold">المستوى الحالي</p>
                                                    <span class="px-2 py-0.5 rounded text-xs font-black {{ $viewUser->getRankBadgeColor() }}">
                                                        {{ $viewUser->getRankLabel() }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-3">
                                            <div class="p-3 bg-white rounded-2xl border border-primary-50 shadow-sm text-center">
                                                <p class="text-[10px] text-primary-400 font-bold mb-1">عدد العملاء</p>
                                                <p class="text-xl font-black text-primary-900">{{ $viewUser->leads_count }}</p>
                                            </div>
                                            <!-- Add more stats if available -->
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-amber-50/30 p-6 rounded-3xl border border-amber-100">
                                    <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest mb-4">المعلومات المالية</h4>
                                    <div class="space-y-3">
                                        <div>
                                            <p class="text-[10px] text-amber-500 font-bold">اسم البنك</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->bank_name ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-amber-500 font-bold">اسم صاحب الحساب</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->account_holder_name ?: '-' }}</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-amber-500 font-bold">رقم الآيبان</p>
                                            <p class="font-mono text-sm font-bold text-primary-900 dir-ltr text-left">{{ $viewUser->iban ?: '-' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 rounded-b-[2.5rem] flex justify-end gap-3">
                        <button @click="showViewModal = false" class="px-8 py-3 bg-white border border-gray-200 text-gray-600 rounded-2xl font-bold hover:bg-gray-50 hover:shadow-sm transition-all text-sm">
                            إغلاق
                        </button>
                        <button wire:click="editUser({{ $viewUser->id }}); showViewModal = false" class="btn btn-primary">
                            تعديل البيانات
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>