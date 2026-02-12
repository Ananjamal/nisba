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

    public $selectedRoles = [];

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
        $this->reset(['name', 'email', 'password', 'selectedRoles', 'userId', 'isEditMode', 'user']);
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
                        'role' => 'الدور الوظيفي',
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
                        @if($columns['role'])
                        <th class="pb-4 font-bold text-left">الدور الوظيفي</th>
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
                        @if($columns['created_at'])
                        <td class="py-4">
                            <div class="font-bold text-primary-600">{{ $user->created_at->format('Y/m/d') }}</div>
                            <div class="text-[10px] text-gray-400 font-bold mt-0.5">{{ $user->created_at->diffForHumans() }}</div>
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-4">
                            <div class="flex gap-2">
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
</div>