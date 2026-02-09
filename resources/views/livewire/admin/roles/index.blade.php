<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Livewire\WithPagination;
use App\Livewire\Traits\WithDynamicTable;
use App\Models\ActivityLog;

use Livewire\Attributes\Computed;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination, WithDynamicTable;

    public $showModal = false;
    public $isEditMode = false;
    public $roleId = null;

    #[Rule('required|min:3|unique:roles,name')]
    public $name = '';

    public $selectedPermissions = [];

    public function mount()
    {
        $this->columns = [
            'name' => true,
            'users_count' => true,
            'permissions_count' => true,
            'created_at' => true,
            'actions' => true,
        ];
    }

    #[Computed]
    public function permissions()
    {
        return Permission::all()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return count($parts) > 1 ? $parts[1] : 'other';
        })->sortKeys();
    }

    public function openCreateModal()
    {
        $this->reset(['name', 'selectedPermissions', 'roleId', 'isEditMode']);
        $this->showModal = true;
    }

    public function openEditModal(Role $role)
    {
        $this->resetValidation();
        $this->roleId = $role->id;
        $this->name = $role->name;
        $this->selectedPermissions = $role->permissions->pluck('name')->toArray();
        $this->isEditMode = true;
        $this->showModal = true;
    }

    public function save()
    {
        $rules = [
            'name' => 'required|min:3|unique:roles,name' . ($this->isEditMode ? ',' . $this->roleId : ''),
        ];

        $this->validate($rules);

        if ($this->isEditMode) {
            $role = Role::findOrFail($this->roleId);

            // Protection check
            if (in_array($role->name, ['super-admin', 'admin', 'affiliate', 'employee']) && $this->name !== $role->name) {
                $this->addError('name', 'لا يمكن تغيير اسم هذا الدور الأساسي');
                return;
            }

            $role->update(['name' => $this->name]);

            if ($role->name !== 'super-admin') {
                $role->syncPermissions($this->selectedPermissions);
            }

            $actionType = 'update_role';
            $description = "تم تحديث الدور: {$role->name}";
        } else {
            $role = Role::create(['name' => $this->name]);
            if (!empty($this->selectedPermissions)) {
                $role->syncPermissions($this->selectedPermissions);
            }
            $actionType = 'create_role';
            $description = "تم إنشاء دور جديد: {$role->name}";
        }

        // Log Activity
        ActivityLog::create([
            'causer_id' => auth()->id(),
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'type' => $actionType,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->showModal = false;
        $this->dispatch('notify', type: 'success', message: $this->isEditMode ? 'تم تحديث الدور بنجاح' : 'تم إنشاء الدور بنجاح');
    }

    public function deleteRole(Role $role)
    {
        if (in_array($role->name, ['super-admin', 'admin', 'affiliate', 'employee'])) {
            $this->dispatch('notify', type: 'error', message: 'لا يمكن حذف هذا الدور الأساسي');
            return;
        }

        if ($role->users()->count() > 0) {
            $this->dispatch('notify', type: 'error', message: 'لا يمكن حذف دور تم تعيينه لمستخدمين');
            return;
        }

        $role->delete();

        ActivityLog::create([
            'causer_id' => auth()->id(),
            'subject_type' => Role::class,
            'subject_id' => $role->id,
            'type' => 'delete_role',
            'description' => "تم حذف الدور: {$role->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        $this->dispatch('notify', type: 'success', message: 'تم حذف الدور بنجاح');
    }

    public function toggleGroup($group)
    {
        $groupPermissions = $this->permissions[$group]->pluck('name')->toArray();
        $hasAll = count(array_intersect($groupPermissions, $this->selectedPermissions)) === count($groupPermissions);

        if ($hasAll) {
            $this->selectedPermissions = array_diff($this->selectedPermissions, $groupPermissions);
        } else {
            $this->selectedPermissions = array_unique(array_merge($this->selectedPermissions, $groupPermissions));
        }
    }

    public function with(): array
    {
        $roles = Role::query()
            ->withCount(['users', 'permissions'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%');
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);

        return [
            'roles' => $roles,
        ];
    }
}; ?>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-3xl font-bold text-gray-900 tracking-tight">الأدوار والصلاحيات</h2>
            <p class="text-gray-500 font-medium mt-1">إدارة أدوار الموظفين وتحديد صلاحياتهم</p>
        </div>
        <div>
            <button wire:click="openCreateModal"
                class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span>إضافة دور جديد</span>
            </button>
        </div>
    </div>

    <!-- Table Container -->
    <div class="bg-white p-8 rounded-3xl shadow-sm border border-gray-100">
        <!-- Filter Bar -->
        <x-table.filter-bar :statusOptions="[]">
            <x-slot name="actions">
                <x-table.column-toggler :columns="$columns" :labels="[
                    'name' => 'اسم الدور',
                    'users_count' => 'عدد المستخدمين',
                    'permissions_count' => 'عدد الصلاحيات',
                    'created_at' => 'تاريخ الإنشاء',
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
                        <x-table.th field="name" :sortField="$sortField" :sortDirection="$sortDirection" label="اسم الدور" />
                        @endif
                        @if($columns['users_count'])
                        <th class="pb-4 font-semibold text-start">عدد المستخدمين</th>
                        @endif
                        @if($columns['permissions_count'])
                        <th class="pb-4 font-semibold text-start">عدد الصلاحيات</th>
                        @endif
                        @if($columns['created_at'])
                        <x-table.th field="created_at" :sortField="$sortField" :sortDirection="$sortDirection" label="تاريخ الإنشاء" />
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-semibold text-start">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($roles as $role)
                    <tr class="group hover:bg-gray-50 transition-all duration-300 border-b border-gray-50 last:border-0">
                        @if($columns['name'])
                        <td class="py-5">
                            <div class="font-bold text-gray-900">{{ $role->name }}</div>
                            <div class="text-xs text-gray-400 font-bold">{{ $role->guard_name }}</div>
                        </td>
                        @endif
                        @if($columns['users_count'])
                        <td class="py-5">
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                {{ $role->users_count }} مستخدم
                            </span>
                        </td>
                        @endif
                        @if($columns['permissions_count'])
                        <td class="py-5">
                            <span class="inline-flex items-center px-3 py-1 rounded-xl text-xs font-bold bg-gray-100 text-gray-700 border border-gray-200">
                                {{ $role->permissions_count }} صلاحية
                            </span>
                        </td>
                        @endif
                        @if($columns['created_at'])
                        <td class="py-5">
                            <div class="font-bold text-gray-900">{{ $role->created_at->format('Y/m/d') }}</div>
                            <div class="text-xs text-gray-400 font-bold">{{ $role->created_at->diffForHumans() }}</div>
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-5">
                            <div class="flex items-center gap-2">
                                <button wire:click="openEditModal({{ $role->id }})"
                                    class="p-2 text-gray-600 bg-gray-100 rounded-lg hover:bg-gray-200 hover:text-gray-800 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                @unless(in_array($role->name, ['super-admin', 'admin', 'affiliate', 'employee']))
                                <button wire:click="deleteRole({{ $role->id }})"
                                    wire:confirm="هل أنت متأكد من حذف هذا الدور؟"
                                    class="p-2 text-rose-600 bg-rose-50 rounded-lg hover:bg-rose-100 hover:text-rose-700 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                                @endunless
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="100" class="py-12 text-center">
                            <div class="flex flex-col items-center justify-center text-gray-500">
                                <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <p class="text-lg font-bold">لا يوجد أدوار معرفة</p>
                                <p class="text-sm mt-1">قم بإضافة دور جديد للبدء</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            {{ $roles->links() }}
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
                class="relative transform overflow-hidden rounded-3xl bg-white text-right shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-100">

                <!-- Modal Header -->
                <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">
                        {{ $isEditMode ? 'تعديل الدور' : 'إضافة دور جديد' }}
                    </h3>
                    <button @click="show = false" class="text-gray-400 hover:text-gray-500 transition-colors">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <form wire:submit="save">
                    <div class="p-6 space-y-6 max-h-[70vh] overflow-y-auto custom-scrollbar">
                        <!-- Role Name -->
                        <div>
                            <label class="block text-sm font-bold text-gray-900 mb-2">اسم الدور <span class="text-red-500">*</span></label>
                            <input type="text" wire:model="name"
                                class="w-full rounded-xl border-gray-200 focus:border-gray-500 focus:ring-gray-500 font-bold text-gray-900 placeholder-gray-400 p-3 bg-gray-50"
                                placeholder="مثال: مدير المبيعات">
                            @error('name') <span class="text-red-500 text-sm font-bold block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <!-- Permissions -->
                        <div>
                            <h4 class="text-base font-bold text-gray-900 mb-4">الصلاحيات</h4>

                            @if($isEditMode && $roleId && \Spatie\Permission\Models\Role::find($roleId)?->name === 'super-admin')
                            <div class="bg-blue-50 text-blue-700 p-4 rounded-xl border border-blue-100 font-bold text-center text-sm">
                                هذا الدور يملك جميع الصلاحيات تلقائياً
                            </div>
                            @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                @foreach($this->permissions as $group => $groupPermissions)
                                <div class="border border-gray-200 rounded-xl overflow-hidden">
                                    <div class="bg-gray-100 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                        <h5 class="font-bold text-gray-800 capitalize text-sm">{{ $group }}</h5>
                                        <button type="button" wire:click="toggleGroup('{{ $group }}')"
                                            class="text-xs font-bold text-gray-600 hover:text-gray-900">
                                            تحديد الكل
                                        </button>
                                    </div>
                                    <div class="p-4 space-y-3 bg-white">
                                        @foreach($groupPermissions as $permission)
                                        <label class="flex items-center gap-3 cursor-pointer group">
                                            <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}"
                                                class="w-4 h-4 rounded border-gray-300 text-gray-900 focus:ring-gray-900 transition-all bg-gray-50">
                                            <span class="font-bold text-gray-600 text-sm group-hover:text-gray-900 transition-colors">
                                                {{ $permission->name }}
                                            </span>
                                        </label>
                                        @endforeach
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
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
                            {{ $isEditMode ? 'حفظ التغييرات' : 'إنشاء الدور' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>