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
        return Permission::all()->groupBy('group')->sortKeys();
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
                        'name' => 'اسم الدور',
                        'users_count' => 'عدد المستخدمين',
                        'permissions_count' => 'عدد الصلاحيات',
                        'created_at' => 'تاريخ الإنشاء',
                        'actions' => 'العمليات'
                    ]" />

                    <button wire:click="openCreateModal" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>إضافة دور جديد</span>
                    </button>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="text-primary-400 text-sm border-b border-primary-50">
                        @if($columns['name'])
                        <x-table.th field="name" :sortField="$sortField" :sortDirection="$sortDirection" label="اسم الدور" />
                        @endif
                        @if($columns['users_count'])
                        <th class="pb-4 font-bold text-left">عدد المستخدمين</th>
                        @endif
                        @if($columns['permissions_count'])
                        <th class="pb-4 font-bold text-left">عدد الصلاحيات</th>
                        @endif
                        @if($columns['created_at'])
                        <x-table.th field="created_at" :sortField="$sortField" :sortDirection="$sortDirection" label="تاريخ الإنشاء" />
                        @endif
                        @if($columns['actions'])
                        <th class="pb-4 font-bold text-left">العمليات</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-primary-50">
                    @forelse($roles as $role)
                    <tr class="group hover:bg-gray-50 transition-colors duration-200">
                        @if($columns['name'])
                        <td class="py-4">
                            <div class="font-bold text-gray-900">{{ $role->name }}</div>
                            <div class="text-[10px] text-gray-400 font-bold mt-0.5">{{ $role->guard_name }}</div>
                        </td>
                        @endif
                        @if($columns['users_count'])
                        <td class="py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-primary-50 text-primary-600 border border-primary-100">
                                {{ $role->users_count }} مستخدم
                            </span>
                        </td>
                        @endif
                        @if($columns['permissions_count'])
                        <td class="py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-primary-50 text-primary-600 border border-primary-100">
                                {{ $role->permissions_count }} صلاحية
                            </span>
                        </td>
                        @endif
                        @if($columns['created_at'])
                        <td class="py-4">
                            <div class="font-bold text-primary-600">{{ $role->created_at->format('Y/m/d') }}</div>
                            <div class="text-[10px] text-gray-400 font-bold mt-0.5">{{ $role->created_at->diffForHumans() }}</div>
                        </td>
                        @endif
                        @if($columns['actions'])
                        <td class="py-4">
                            <div class="flex gap-2">
                                <button wire:click="openEditModal({{ $role->id }})" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </button>

                                @unless(in_array($role->name, ['super-admin', 'admin', 'affiliate', 'employee']))
                                <button wire:click="deleteRole({{ $role->id }})" wire:confirm="هل أنت متأكد من حذف هذا الدور؟" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all duration-300" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                        <td colspan="10" class="py-12 text-center">
                            <p class="text-primary-400 font-bold">لا يوجد أدوار معرفة</p>
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
        <div class="relative bg-white rounded-2xl w-full max-w-4xl shadow-2xl mt-4 mb-6 flex flex-col border-2 border-gray-200 max-h-[90vh]"
            @click.away="show = false"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

            <!-- Header -->
            <div class="bg-gradient-to-b from-gray-50 to-white px-8 py-6 flex justify-between items-center flex-shrink-0 border-b border-gray-200">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800">{{ $isEditMode ? 'تعديل الدور' : 'إضافة دور جديد' }}</h3>
                    <p class="text-gray-500 text-sm mt-1.5">{{ $isEditMode ? 'تحديث صلاحيات الدور' : 'إنشاء دور جديد وتحديد الصلاحيات' }}</p>
                </div>
                <button @click="show = false" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2.5 rounded-xl transition-all duration-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Body -->
            <form wire:submit="save" class="flex flex-col flex-1 overflow-hidden">
                <div class="p-8 space-y-6 overflow-y-auto bg-white flex-1">
                    <!-- Role Name -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2.5">اسم الدور <span class="text-red-500">*</span></label>
                        <input type="text" wire:model="name"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all bg-gray-50 focus:bg-white"
                            placeholder="مثال: مدير المبيعات">
                        @error('name') <span class="text-red-500 text-xs mt-1 block">{{ $message }}</span> @enderror
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
                            <div class="border border-gray-200 rounded-xl overflow-hidden bg-white shadow-sm">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 flex items-center justify-between">
                                    <h5 class="font-bold text-gray-800 capitalize text-sm">{{ $group }}</h5>
                                    <button type="button" wire:click="toggleGroup('{{ $group }}')"
                                        class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                                        تحديد الكل
                                    </button>
                                </div>
                                <div class="p-4 space-y-3">
                                    @foreach($groupPermissions as $permission)
                                    <label class="flex items-center gap-3 cursor-pointer group">
                                        <input type="checkbox" wire:model="selectedPermissions" value="{{ $permission->name }}"
                                            class="w-4.5 h-4.5 rounded border-gray-300 text-blue-600 focus:ring-blue-500 transition-all bg-white group-hover:border-blue-400">
                                        <span class="font-bold text-gray-600 text-xs group-hover:text-gray-900 transition-colors">
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

                <!-- Footer -->
                <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50 rounded-b-2xl flex-shrink-0">
                    <button type="button" @click="show = false"
                        class="px-6 py-3 rounded-xl border border-gray-300 text-gray-700 hover:bg-white hover:border-gray-400 font-semibold transition-all duration-200 flex items-center gap-2">
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