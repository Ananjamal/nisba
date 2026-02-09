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

    #[Rule('required|min:3')]
    public $name = '';

    public $email = '';
    public $password = '';

    #[Rule('required|exists:roles,name')]
    public $role = '';

    public $roles = [];

    public function mount()
    {
        $this->columns = [
            'name' => true,
            'email' => true,
            'role' => true,
            'created_at' => true,
            'actions' => true,
        ];
        $this->sortField = 'created_at';
        $this->roles = Role::whereNotIn('name', ['affiliate'])->get();
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'email', 'password', 'role', 'userId', 'isEditMode', 'user']);
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
        $this->role = $user->roles->first()?->name;
        $this->password = ''; // Reset password field
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min:3',
            'email' => ['required', 'email', ValidationRule::unique('users')->ignore($this->userId)],
            'role' => 'required|exists:roles,name',
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
            ];

            if (!empty($this->password)) {
                $data['password'] = Hash::make($this->password);
            }

            $this->user->update($data);
            $this->user->syncRoles([$this->role]);

            $actionType = 'update_staff';
            $description = "تم تحديث بيانات الموظف: {$this->user->name}";
            $subjectId = $this->user->id;
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => Hash::make($this->password),
                'email_verified_at' => now(),
            ]);

            $user->assignRole($this->role);

            $actionType = 'create_staff';
            $description = "تم إضافة موظف جديد: {$user->name} بدور {$this->role}";
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

    public function with(): array
    {
        $users = User::query()
            ->where('role', '!=', 'affiliate')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return [
            'users' => $users,
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">إدارة الموظفين</h2>
            <p class="text-gray-500 font-medium mt-1">عرض وإدارة الحسابات الإدارية والموظفين</p>
        </div>
        <div>
            <button wire:click="openCreateModal"
                class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>إضافة موظف</span>
            </button>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">

        <!-- Filter Bar -->
        <x-table.filter-bar :statusOptions="[]">
            <x-slot name="actions">
                <x-table.column-toggler :columns="$columns" :labels="[
                    'name' => 'الاسم',
                    'email' => 'البريد الإلكتروني',
                    'role' => 'الدور الوظيفي',
                    'created_at' => 'تاريخ الانضمام',
                    'actions' => 'العمليات'
                ]" />
            </x-slot>
        </x-table.filter-bar>

        <!-- Table -->
        <div class="overflow-x-auto mt-6">
            <table class="w-full">
                <thead>
                    <tr class="text-gray-500 text-sm border-b border-gray-100">
                        @if($columns['name'])
                        <x-table.th field="name" :sortField="$sortField" :sortDirection="$sortDirection" label="الاسم" />
                        @endif
                        @if($columns['email'])
                        <x-table.th field="email" :sortField="$sortField" :sortDirection="$sortDirection" label="البريد الإلكتروني" />
                        @endif
                        @if($columns['role'])
                        <th class="pb-4 font-semibold text-start">الدور الوظيفي</th>
                        @endif
                        @if($columns['created_at'])
                        <x-table.th field="created_at" :sortField="$sortField" :sortDirection="$sortDirection" label="تاريخ الانضمام" />
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-semibold text-start">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $user)
                    <tr class="group hover:bg-gray-50 transition-all duration-300 border-b border-gray-50 last:border-0">
                        @if($columns['name'])
                        <td class="py-5">
                            <div class="font-bold text-gray-900">{{ $user->name }}</div>
                        </td>
                        @endif
                        @if($columns['email'])
                        <td class="py-5">
                            <div class="text-sm text-gray-500 font-bold">{{ $user->email }}</div>
                        </td>
                        @endif
                        @if($columns['role'])
                        <td class="py-5">
                            <div class="flex flex-wrap gap-1">
                                @forelse($user->roles as $role)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                    {{ $role->name }}
                                </span>
                                @empty
                                <span class="text-xs text-gray-400">لا يوجد دور</span>
                                @endforelse
                            </div>
                        </td>
                        @endif
                        @if($columns['created_at'])
                        <td class="py-5">
                            <div class="font-bold text-gray-900">{{ $user->created_at->format('Y/m/d') }}</div>
                            <div class="text-xs text-gray-400 font-bold">{{ $user->created_at->diffForHumans() }}</div>
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-5">
                            <div class="flex items-center gap-2">
                                <button wire:click="openEditModal({{ $user->id }})"
                                    class="p-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                <button wire:click="deleteUser({{ $user->id }})"
                                    wire:confirm="هل أنت متأكد من حذف هذا الموظف؟"
                                    class="p-2 text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 hover:text-rose-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                </svg>
                                <p class="text-lg font-bold">لا يوجد موظفين</p>
                                <p class="text-sm mt-1">قم بإضافة موظف جديد للبدء</p>
                            </div>
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
    <div x-data="{ show: @entangle('showModal') }"
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-50 overflow-y-auto"
        style="display: none;">

        <div class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm transition-opacity" @click="show = false"></div>

        <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
            <div x-show="show"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative transform overflow-hidden rounded-3xl bg-white text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl border border-gray-100">

                <!-- Modal Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $isEditMode ? 'تعديل موظف' : 'إضافة موظف جديد' }}
                    </h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="save">
                    <div class="p-6 space-y-6">

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">الاسم الكامل <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name"
                                class="w-full rounded-xl border-gray-200 focus:border-gray-500 focus:ring-gray-500 font-bold text-gray-900 placeholder-gray-400 p-3 bg-gray-50"
                                placeholder="الاسم الكامل">
                            @error('name') <span class="text-red-500 text-sm font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">البريد الإلكتروني <span class="text-red-500">*</span></label>
                            <input type="email" wire:model="email"
                                class="w-full rounded-xl border-gray-200 focus:border-gray-500 focus:ring-gray-500 font-bold text-gray-900 placeholder-gray-400 p-3 bg-gray-50 text-left"
                                placeholder="email@example.com" dir="ltr">
                            @error('email') <span class="text-red-500 text-sm font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">
                                كلمة المرور
                                @if($isEditMode) <span class="text-xs font-normal text-gray-500">(اختياري)</span> @else <span class="text-red-500">*</span> @endif
                            </label>
                            <input type="password" wire:model="password"
                                class="w-full rounded-xl border-gray-200 focus:border-gray-500 focus:ring-gray-500 font-bold text-gray-900 placeholder-gray-400 p-3 bg-gray-50"
                                placeholder="••••••••">
                            @error('password') <span class="text-red-500 text-sm font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">الدور الوظيفي <span class="text-red-500">*</span></label>
                            <div class="relative">
                                <select wire:model="role"
                                    class="w-full appearance-none rounded-xl border-gray-200 focus:border-gray-500 focus:ring-gray-500 font-bold text-gray-900 p-3 pr-10 bg-gray-50">
                                    <option value="">اختر الدور...</option>
                                    @foreach($roles as $r)
                                    <option value="{{ $r->name }}">{{ $r->name }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center px-3 text-gray-500">
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                    </svg>
                                </div>
                            </div>
                            @error('role') <span class="text-red-500 text-sm font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                    </div>

                    <!-- Modal Footer -->
                    <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                        <button type="button" @click="show = false"
                            class="px-5 py-2.5 rounded-xl border border-gray-300 text-gray-700 hover:bg-white hover:border-gray-400 font-bold transition-all shadow-sm text-sm">
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
    </div>
</div>