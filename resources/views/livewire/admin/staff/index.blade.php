<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\WithPagination;
use App\Livewire\Traits\WithDynamicTable;
use App\Models\ActivityLog;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination, WithDynamicTable;

    public $showModal = false;
    public $isEditMode = false;
    public $userId = null;
    public $user = null;

    // View Modal State
    public $showViewModal = false;
    public $viewUser = null;
    public $userActivity = [];

    #[Rule('required|min:3')]
    public $name = '';

    public $email = '';
    public $phone = '';
    public $password = '';

    public $selectedRoles = [];

    public $roles = [];

    public function mount()
    {
        $this->columns = [
            'name' => true,
            'email' => true,
            'phone' => true,
            'role' => true,
            'status' => true,
            'created_at' => true,
            'actions' => true,
        ];
        $this->sortField = 'created_at';
        $this->roles = Role::whereNotIn('name', ['affiliate'])->get();
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'email', 'phone', 'password', 'selectedRoles', 'userId', 'isEditMode', 'user']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function openEditModal(User $user)
    {
        if ($user->hasRole('super-admin') && !auth()->user()->hasRole('super-admin')) {
            $this->dispatch('notify', type: 'error', message: 'ليس لديك صلاحية تعديل هذا الستحدم');
            return;
        }

        $this->resetValidation();
        $this->user = $user;
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->selectedRoles = $user->roles->pluck('name')->toArray();
        $this->password = ''; // Reset password field
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min:3',
            'email' => ['required', 'email', ValidationRule::unique('users')->ignore($this->userId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'selectedRoles' => 'required|array|min:1',
            'selectedRoles.*' => 'exists:roles,name',
        ];

        if (!$this->isEditMode) {
            $rules['password'] = 'required|min:8';
        } else {
            $rules['password'] = 'nullable|min:8';
        }

        $this->validate($rules);

        if ($this->isEditMode) {
            $data = [
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
            ];

            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $this->user->update($data);
            $this->user->syncRoles($this->selectedRoles);

            $actionType = 'update_staff';
            $description = "تم تحديث بيانات الموظف: {$this->user->name}";
            $subjectId = $this->user->id;
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'phone' => $this->phone,
                'password' => Hash::make($this->password),
                'role' => $this->selectedRoles[0] ?? 'employee',
                'status' => 'active',
                'email_verified_at' => now(),
            ]);

            $user->assignRole($this->selectedRoles);

            $actionType = 'create_staff';
            $description = "تم إضافة موظف جديد: {$user->name} بأدوار " . implode(', ', $this->selectedRoles);
            $subjectId = $user->id;
        }

        // Log Activity
        ActivityLog::create([
            'causer_id' => auth()->id(),
            'subject_type' => User::class,
            'subject_id' => $subjectId,
            'type' => $actionType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->showModal = false;
        $this->dispatch('notify', type: 'success', message: $this->isEditMode ? 'تم تحديث الموظف بنجاح' : 'تم إضافة الموظف بنجاح');
    }

    public function deleteUser(User $user)
    {
        if ($user->id === auth()->id()) {
            $this->dispatch('notify', type: 'error', message: 'لا يمكنك حذف حسابك الحالي');
            return;
        }

        if ($user->hasRole('super-admin')) {
            $this->dispatch('notify', type: 'error', message: 'لا يمكن حذف حساب المسؤول الرئيسي');
            return;
        }

        $user->delete();

        ActivityLog::create([
            'causer_id' => auth()->id(),
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'type' => 'delete_staff',
            'description' => "تم حذف الموظف: {$user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'تم حذف الموظف بنجاح');
    }

    public function openViewModal(User $user)
    {
        $this->viewUser = $user;
        $this->userActivity = ActivityLog::where('causer_id', $user->id)
            ->orWhere(function ($query) use ($user) {
                $query->where('subject_type', User::class)
                    ->where('subject_id', $user->id);
            })
            ->latest()
            ->limit(5)
            ->get();
        $this->showViewModal = true;
    }

    public function closeViewModal()
    {
        $this->showViewModal = false;
        $this->viewUser = null;
        $this->userActivity = [];
    }

    public function with(): array
    {
        $users = User::query()
            ->where('role', '!=', 'affiliate')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('phone', 'like', '%' . $this->search . '%');
                });
            })
            ->with(['roles'])
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return [
            'users' => $users,
        ];
    }
}; ?>

<div class="space-y-8" x-data="{ show: @entangle('showModal') }">
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
                        'name' => 'الاسم',
                        'email' => 'البريد الإلكتروني',
                        'phone' => 'رقم الهاتف',
                        'role' => 'الدور الوظيفي',
                        'status' => 'الحالة',
                        'created_at' => 'تاريخ الانضمام',
                        'actions' => 'العمليات'
                    ]" />

                    <button wire:click="openCreateModal" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>إضافة موظف</span>
                    </button>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-primary-400 text-sm border-b border-primary-50">
                        @if($columns['name'])
                        <x-table.th field="name" :sortField="$sortField" :sortDirection="$sortDirection" label="الاسم" />
                        @endif
                        @if($columns['email'])
                        <x-table.th field="email" :sortField="$sortField" :sortDirection="$sortDirection" label="البريد الإلكتروني" />
                        @endif
                        @if($columns['phone'])
                        <x-table.th field="phone" :sortField="$sortField" :sortDirection="$sortDirection" label="رقم الهاتف" />
                        @endif
                        @if($columns['role'])
                        <th class="pb-4 font-bold text-right">الدور الوظيفي</th>
                        @endif
                        @if($columns['status'])
                        <x-table.th field="status" :sortField="$sortField" :sortDirection="$sortDirection" label="الحالة" />
                        @endif
                        @if($columns['created_at'])
                        <x-table.th field="created_at" :sortField="$sortField" :sortDirection="$sortDirection" label="تاريخ الانضمام" />
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-bold text-left">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($users as $user)
                    <tr class="group hover:bg-gray-50 transition-colors duration-200">
                        @if($columns['name'])
                        <td class="py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full bg-primary-50 flex items-center justify-center text-primary-600 font-bold">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span class="font-bold text-gray-900 block hover:text-primary-600 transition text-right">{{ $user->name }}</span>
                            </div>
                        </td>
                        @endif
                        @if($columns['email'])
                        <td class="py-4 font-bold text-primary-900">{{ $user->email }}</td>
                        @endif
                        @if($columns['phone'])
                        <td class="py-4 font-bold text-primary-900" dir="ltr text-right">{{ $user->phone ?? '-' }}</td>
                        @endif
                        @if($columns['role'])
                        <td class="py-4">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-[10px] font-black bg-primary-50 text-primary-600 border border-primary-100">
                                    {{ $role->name }}
                                </span>
                                @empty
                                <span class="text-xs text-gray-400">لا يوجد دور</span>
                                @endforelse
                            </div>
                        </td>
                        @endif
                        @if($columns['status'])
                        <td class="py-4">
                            @if($user->status === 'active')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-green-50 text-green-600 border border-green-100">
                                نشط
                            </span>
                            @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-black bg-rose-50 text-rose-600 border border-rose-100">
                                غير نشط
                            </span>
                            @endif
                        </td>
                        @endif
                        @if($columns['created_at'])
                        <td class="py-4">
                            <div class="font-bold text-primary-600">{{ $user->created_at->format('Y/m/d') }}</div>
                            <div class="text-[10px] text-gray-400 font-bold mt-0.5">{{ $user->created_at->diffForHumans() }}</div>
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
                                <button wire:click="openEditModal({{ $user->id }})" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>
                                <button wire:click="deleteUser({{ $user->id }})" wire:confirm="هل أنت متأكد من حذف هذا الموظف؟" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all duration-300" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="py-12 text-center">
                            <p class="text-primary-400 font-bold">لا يوجد موظفين</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Modal -->
    <div x-show="show"
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

            <!-- Header -->
            <div class="bg-gradient-to-b from-gray-50 to-white px-8 py-6 flex justify-between items-center flex-shrink-0 border-b border-gray-200">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $isEditMode ? 'تعديل موظف' : 'إضافة موظف جديد' }}</h3>
                    <p class="text-gray-500 text-sm mt-1.5">{{ $isEditMode ? 'تحديث بيانات الموظف والصلاحيات' : 'إضافة موظف جديد للصلاحيات' }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2.5 rounded-xl transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <form wire:submit="save" class="flex flex-col flex-1">
                <div class="p-8 space-y-6 overflow-y-auto bg-white flex-1">
                    <!-- Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">الاسم الكامل <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white"
                            placeholder="الاسم الكامل">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">البريد الإلكتروني <span class="text-red-500">*</span></label>
                        <input type="email" wire:model="email"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white text-left"
                            placeholder="email@example.com" dir="ltr">
                        @error('email') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">رقم الهاتف</label>
                        <input type="text" wire:model="phone"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white text-left"
                            placeholder="05xxxxxxxx" dir="ltr">
                        @error('phone') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">
                            كلمة المرور
                            @if($isEditMode) <span class="text-xs font-normal text-gray-500">(اختياري)</span> @else <span class="text-red-500">*</span> @endif
                        </label>
                        <input type="password" wire:model="password"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white"
                            placeholder="••••••••">
                        @error('password') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
                    </div>

                    <!-- Roles -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">الأدوار الوظيفية <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 gap-3 bg-gray-50 p-4 rounded-xl border border-gray-200">
                            @foreach($roles as $r)
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

                <!-- Footer -->
                <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl">
                    <button type="button" @click="show = false"
                        class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-white hover:border-gray-400 font-semibold transition-all duration-200 flex items-center gap-2">
                        إلغاء
                    </button>
                    <button type="submit"
                        class="btn btn-primary">
                        {{ $isEditMode ? 'حفظ التغييرات' : 'إضافة الموظف' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Professional View Details Modal -->
    <template x-teleport="body">
        <div x-show="$wire.showViewModal"
            x-on:keydown.escape.window="$wire.closeViewModal()"
            class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-900/60 backdrop-blur-sm transition-opacity" @click="$wire.closeViewModal()"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl transform overflow-hidden rounded-[2.5rem] bg-white shadow-2xl transition-all"
                    x-show="$wire.showViewModal"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100">

                    @if($viewUser)
                    <!-- Leads-Style Modal Header -->
                    <div class="px-8 py-6 border-b border-primary-50 flex items-center justify-between bg-gradient-to-r from-primary-50 to-white">
                        <div>
                            <h3 class="text-2xl font-black text-primary-900">تفاصيل الموظف</h3>
                            <p class="text-primary-500 text-sm font-medium">عرض المعلومات الكاملة للموظف والنشاطات</p>
                        </div>
                        <button @click="$wire.closeViewModal()" class="p-2 rounded-full hover:bg-white hover:shadow-md transition-all text-primary-400 hover:text-primary-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-8 max-h-[70vh] overflow-y-auto bg-white">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <!-- Staff Basic Info -->
                            <div class="space-y-6">
                                <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                    <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">معلومات الموظف</h4>
                                    <div class="space-y-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-xl bg-white shadow-sm border border-primary-100 flex items-center justify-center text-primary-600">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <p class="text-[10px] text-primary-400 font-bold">الاسم الكامل</p>
                                                <p class="font-black text-primary-900 text-lg line-height-1">{{ $viewUser->name }}</p>
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
                                                <p class="font-black text-primary-900 text-sm truncate">{{ $viewUser->email }}</p>
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
                                                <p class="font-black text-primary-900 text-lg leading-none" dir="ltr">{{ $viewUser->phone ?: '-' }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="bg-gray-50/50 p-6 rounded-3xl border border-gray-100">
                                    <h4 class="text-xs font-black text-gray-400 uppercase tracking-widest mb-4">تفاصيل إضافية</h4>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">الحالة</p>
                                            @if($viewUser->status === 'active')
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-green-600 bg-green-50 px-2 py-1 rounded-lg border border-green-100">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                                                نشط
                                            </span>
                                            @else
                                            <span class="inline-flex items-center gap-1.5 text-[10px] font-black text-rose-600 bg-rose-50 px-2 py-1 rounded-lg border border-rose-100">
                                                غير نشط
                                            </span>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-gray-400 font-bold">تاريخ الانضمام</p>
                                            <p class="font-black text-primary-900 text-sm">{{ $viewUser->created_at->format('Y-m-d') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Roles & Context -->
                            <div class="space-y-6">
                                <div class="bg-primary-50/50 p-6 rounded-3xl border border-primary-100">
                                    <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-4">الأدوار الوظيفية</h4>
                                    <div class="flex flex-wrap gap-2">
                                        @forelse($viewUser->roles as $role)
                                        <div class="px-4 py-2 bg-white rounded-2xl border border-primary-50 shadow-sm flex items-center gap-2">
                                            <div class="w-2 h-2 rounded-full bg-primary-400"></div>
                                            <span class="text-xs font-black text-primary-900">{{ $role->name }}</span>
                                        </div>
                                        @empty
                                        <p class="text-xs text-gray-400 font-bold">لا توجد أدوار محددة</p>
                                        @endforelse
                                    </div>
                                </div>

                                <div class="bg-amber-50/30 p-6 rounded-3xl border border-amber-100">
                                    <h4 class="text-xs font-black text-amber-500 uppercase tracking-widest mb-4">ملخص النشاط</h4>
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 rounded-2xl bg-white shadow-sm border border-amber-100 flex items-center justify-center text-amber-500">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div>
                                            <p class="text-[10px] text-amber-500 font-bold">آخر ظهور</p>
                                            <p class="font-black text-primary-900">{{ $viewUser->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Timeline (Leads Style) -->
                        <div class="mt-8">
                            <h4 class="text-xs font-black text-primary-400 uppercase tracking-widest mb-6 flex items-center gap-2">
                                <span class="w-8 h-px bg-primary-100"></span>
                                سجل النشاطات الأخير
                                <span class="w-8 h-px bg-primary-100"></span>
                            </h4>

                            <div class="space-y-4">
                                @forelse($userActivity as $activity)
                                <div class="flex gap-4 group">
                                    <div class="flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-xl bg-primary-50 border border-primary-100 flex items-center justify-center text-primary-600 transition-colors group-hover:bg-primary-600 group-hover:text-white">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        </div>
                                        <div class="w-px h-full bg-primary-50 group-last:hidden mt-1"></div>
                                    </div>
                                    <div class="flex-1 pb-6">
                                        <div class="flex justify-between items-center mb-1">
                                            <span class="text-[10px] font-black text-primary-600 uppercase">{{ $activity->type }}</span>
                                            <span class="text-[9px] font-bold text-gray-400 uppercase">{{ $activity->created_at->diffForHumans() }}</span>
                                        </div>
                                        <div class="p-4 bg-gray-50/50 rounded-2xl border border-gray-100 group-hover:border-primary-100 transition-colors">
                                            <p class="text-sm font-bold text-gray-700 leading-relaxed">{{ $activity->description }}</p>
                                            @if($activity->ip_address)
                                            <div class="mt-2 text-[9px] text-gray-400 font-black flex items-center gap-1.5">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                                                </svg>
                                                IP: {{ $activity->ip_address }}
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @empty
                                <div class="text-center py-6 bg-gray-50 rounded-3xl border border-dashed border-gray-200">
                                    <p class="text-xs text-gray-400 font-bold italic">لا توجد نشاطات مسجلة</p>
                                </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="px-8 py-6 bg-gray-50 border-t border-gray-100 rounded-b-[2.5rem] flex justify-end gap-3">
                        <button @click="$wire.closeViewModal()" class="btn btn-secondary">
                            إغلاق
                        </button>
                        <!-- <button wire:click="openEditModal({{ $viewUser->id }})" class="btn btn-primary">
                            تعديل البيانات
                        </button> -->
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </template>
</div>