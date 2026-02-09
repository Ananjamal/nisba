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
    public $role = 'affiliate';
    public $sector = '';
    public $sector_filter = '';
    public $showModal = false;

    public function mount()
    {
        $this->loadTablePrefs([
            'marketer' => true,
            'sector' => true,
            'email' => true,
            'joined_at' => true,
            'status' => true,
            'actions' => true,
        ]);
    }

    public function with()
    {
        return [
            'users' => User::where('role', 'affiliate')
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
        $this->role = 'affiliate';
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
        $this->role = $user->role ?: 'affiliate';
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
            'role' => 'required|in:admin,affiliate',
            'sector' => 'nullable|string|max:255',
        ]);

        if ($this->userId) {
            $user = User::findOrFail($this->userId);
            $user->update([
                'name' => $this->name,
                'email' => $this->email,
                'sector' => $this->sector,
                'role' => $this->role,
            ]);
            if ($this->password) {
                $user->update(['password' => bcrypt($this->password)]);
            }
            $user->syncRoles([$this->role]);
        } else {
            $user = User::create([
                'name' => $this->name,
                'email' => $this->email,
                'sector' => $this->sector,
                'role' => $this->role,
                'password' => bcrypt($this->password),
            ]);
            $user->assignRole($this->role);
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
            <div class="relative w-full md:w-auto min-w-[160px] group">
                <div class="absolute inset-y-0 right-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                </div>
                <select wire:model.live="sector_filter" class="w-full appearance-none pl-9 pr-10 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 cursor-pointer shadow-sm transition-all text-sm font-bold text-gray-700 hover:border-gray-300 hover:text-gray-900">
                    <option value="">كل القطاعات</option>
                    @foreach(['العقارات', 'التقنية والبرمجة', 'التسويق والدعاية', 'التجارة الإلكترونية', 'التعليم', 'الصحة', 'الخدمات المالية', 'المقاولات والبناء', 'المطاعم والكافيهات', 'أخرى'] as $sec)
                    <option value="{{ $sec }}">{{ $sec }}</option>
                    @endforeach
                </select>
                <div class="absolute inset-y-0 left-3.5 flex items-center pointer-events-none text-gray-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>
            </div>
            <x-slot name="actions">
                <div class="flex gap-2">
                    <x-table.column-toggler :columns="$columns" :labels="[
                    'marketer' => 'المسوق',
                    'sector' => 'القطاع',
                    'email' => 'البريد الإلكتروني',
                    'joined_at' => 'تاريخ التسجيل',
                    'status' => 'الحالة',
                    'actions' => 'العمليات'
                ]" />

                    <button wire:click="createUser" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        <span>إضافة مسوق</span>
                    </button>
                </div>
            </x-slot>
        </x-table.filter-bar>

        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <thead>
                        <tr class="text-primary-400 text-sm border-b border-primary-50">
                            @if($columns['marketer'])
                            <x-table.th field="name" :sortField="$sortField" :sortDirection="$sortDirection" label="المسوق" />
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
                            <x-table.th field="status" :sortField="$sortField" :sortDirection="$sortDirection" label="الحالة" />
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
                                <a href="{{ route('admin.affiliates.show', $user->id) }}" class="font-bold text-gray-900 block hover:text-primary-600 transition text-right">{{ $user->name }}</a>
                            </div>
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
                                <a href="{{ route('admin.affiliates.show', $user->id) }}" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="عرض التفاصيل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                </a>
                                <button wire:click="editUser({{ $user->id }})" class="p-2 text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-xl transition-all duration-300" title="تعديل">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </button>
                                <button wire:confirm="هل أنت متأكد من حذف هذا المستخدم؟" wire:click="deleteUser({{ $user->id }})" class="p-2 text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl transition-all duration-300" title="حذف">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                        @endif
                    </tr>
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
    <template x-teleport="body">
        <div x-show="showModal"
            x-on:keydown.escape.window="showModal = false"
            class="fixed inset-0 z-[1000] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-primary-950/60 backdrop-blur-md transition-opacity" @click="showModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-lg transform overflow-hidden rounded-[2.5rem] bg-white p-10 shadow-2xl transition-all"
                    @click.away="showModal = false">
                    <div class="absolute top-4 right-4">
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-primary-50 text-primary-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-primary-900">{{ $userId ? __('تعديل بيانات المسوق') : __('إضافة مسوق جديد') }}</h3>
                    </div>

                    <form wire:submit="saveUser" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-primary-900 mb-2">{{ __('الاسم الكامل') }}</label>
                            <input type="text" wire:model="name" class="w-full rounded-xl border-primary-200 focus:border-primary-500 focus:ring-primary-500 font-bold text-primary-900" placeholder="{{ __('اسم المسوق') }}">
                            @error('name') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-primary-900 mb-2">{{ __('البريد الإلكتروني') }}</label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border-primary-200 focus:border-primary-500 focus:ring-primary-500 font-bold text-primary-900" placeholder="email@example.com">
                            @error('email') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-primary-900 mb-2">{{ __('المجال / القطاع') }}</label>
                            <select wire:model="sector" class="w-full rounded-xl border-primary-200 focus:border-primary-500 focus:ring-primary-500 font-bold text-primary-900">
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
                            @error('sector') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-primary-900 mb-2">{{ __('كلمة المرور') }} <span class="text-xs text-gray-400 font-normal">{{ $userId ? __('(اتركها فارغة لعدم التغيير)') : '' }}</span></label>
                            <input type="password" wire:model="password" class="w-full rounded-xl border-primary-200 focus:border-primary-500 focus:ring-primary-500 font-bold text-primary-900" placeholder="********">
                            @error('password') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-primary-900 mb-2">{{ __('الصلاحية / الرتبة') }}</label>
                            <select wire:model="role" class="w-full rounded-xl border-primary-200 focus:border-primary-500 focus:ring-primary-500 font-bold text-primary-900">
                                <option value="affiliate">مسوق (Affiliate)</option>
                                <option value="admin">مدير (Admin)</option>
                            </select>
                            @error('role') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full btn btn-primary">
                            <span wire:loading.remove>{{ $userId ? __('حفظ التغييرات') : __('إنشاء الحساب') }}</span>
                            <span wire:loading class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('جاري المعالجة...') }}
                            </span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </template>

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
</div>