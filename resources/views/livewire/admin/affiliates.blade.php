<?php

use Livewire\Volt\Component;
use App\Models\User;
use Livewire\WithPagination;

use Livewire\Attributes\Layout;

new #[Layout('layouts.admin')] class extends Component {
    use WithPagination;

    public $userId = null;
    public $name = '';
    public $email = '';
    public $password = '';
    public $showModal = false;

    public function with()
    {
        return [
            'users' => User::where('role', 'affiliate')->latest()->paginate(10),
        ];
    }

    public function resetForm()
    {
        $this->userId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
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
        $this->password = ''; // Don't show password
        $this->resetValidation();
        $this->showModal = true;
    }

    public function saveUser()
    {
        if ($this->userId) {
            $this->validate([
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users,email,' . $this->userId,
                'password' => 'nullable|min:8',
            ]);

            $user = User::findOrFail($this->userId);
            $user->name = $this->name;
            $user->email = $this->email;
            if ($this->password) {
                $user->password = bcrypt($this->password);
            }
            $user->save();

            session()->flash('message', 'تم تحديث بيانات المسوق بنجاح!');
        } else {
            $this->validate([
                'name' => 'required|min:3',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|min:8',
            ]);

            User::create([
                'name' => $this->name,
                'email' => $this->email,
                'password' => bcrypt($this->password),
                'role' => 'affiliate',
            ]);

            session()->flash('message', 'تم إضافة المسوق بنجاح!');
        }

        $this->showModal = false;
        $this->dispatch('close-modal');
    }

    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        session()->flash('message', 'تم حذف المسوق بنجاح!');
    }
}; ?>

<div class="space-y-8" x-data="{ showModal: @entangle('showModal') }">
    @if (session()->has('message'))
    <div class="p-4 text-sm text-green-700 bg-green-100 rounded-2xl font-bold border border-green-200" role="alert">
        {{ session('message') }}
    </div>
    @endif

    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-3xl font-black text-blue-900 tracking-tight">{{ __('ادارة المسوقين') }}</h2>
            <p class="text-blue-500 font-medium mt-1">{{ __('قائمة بجميع المسوقين المسجلين في النظام') }}</p>
        </div>
        <div class="flex gap-4">
            <button wire:click="createUser" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold transition-all shadow-lg hover:shadow-blue-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                </svg>
                <span>{{ __('إضافة مسوق جديد') }}</span>
            </button>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] border border-blue-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead>
                    <tr class="bg-blue-50 border-b border-blue-100">
                        <th class="px-8 py-5 text-sm font-black text-blue-900">{{ __('المسوق') }}</th>
                        <th class="px-8 py-5 text-sm font-black text-blue-900">{{ __('البريد الإلكتروني') }}</th>
                        <th class="px-8 py-5 text-sm font-black text-blue-900">{{ __('تاريخ التسجيل') }}</th>
                        <th class="px-8 py-5 text-sm font-black text-blue-900">{{ __('الحالة') }}</th>
                        <th class="px-8 py-5 text-sm font-black text-blue-900">{{ __('الإجراءات') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @foreach($users as $user)
                    <tr class="hover:bg-blue-50/50 transition-colors group">
                        <td class="px-8 py-5">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center text-blue-600 font-black text-lg">
                                    {{ substr($user->name, 0, 1) }}
                                </div>
                                <span class="font-bold text-blue-900">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-8 py-5 font-medium text-blue-600">{{ $user->email }}</td>
                        <td class="px-8 py-5 font-bold text-blue-400 text-sm">{{ $user->created_at->format('Y-m-d') }}</td>
                        <td class="px-8 py-5">
                            <span class="px-3 py-1 rounded-lg text-xs font-black bg-emerald-50 text-emerald-600 border border-emerald-100">
                                {{ __('نشط') }}
                            </span>
                        </td>
                        <td class="px-8 py-5">
                            <div x-data="{ open: false }" class="relative">
                                <button @click="open = !open" @click.away="open = false" class="p-2 text-blue-400 hover:text-blue-600 transition-colors rounded-lg hover:bg-blue-50">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                    </svg>
                                </button>
                                <div x-show="open"
                                    class="absolute left-0 mt-2 w-48 bg-white rounded-xl shadow-lg border border-blue-100 z-20 py-1"
                                    style="display: none;">
                                    <button wire:click="editUser({{ $user->id }}); open = false" class="w-full text-right px-4 py-2 text-sm font-bold text-blue-700 hover:bg-blue-50 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                        </svg>
                                        {{ __('تعديل') }}
                                    </button>
                                    <button wire:click="deleteUser({{ $user->id }}); open = false" wire:confirm="{{ __('هل أنت متأكد من حذف هذا المسوق؟') }}" class="w-full text-right px-4 py-2 text-sm font-bold text-red-600 hover:bg-red-50 flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        {{ __('حذف') }}
                                    </button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="px-8 py-5 border-t border-blue-100">
            {{ $users->links() }}
        </div>
    </div>

    <!-- Create/Edit User Modal -->
    <template x-teleport="body">
        <div x-show="showModal" class="fixed inset-0 z-[100] overflow-y-auto" style="display: none;">
            <div class="fixed inset-0 bg-blue-900/60 backdrop-blur-sm transition-opacity" @click="showModal = false"></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-sm transform overflow-hidden rounded-3xl bg-white p-6 shadow-2xl transition-all">
                    <div class="absolute top-4 right-4">
                        <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="mb-6 text-center">
                        <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-blue-600">
                            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-black text-blue-900">{{ $userId ? __('تعديل بيانات المسوق') : __('إضافة مسوق جديد') }}</h3>
                    </div>

                    <form wire:submit="saveUser" class="space-y-4">
                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-2">{{ __('الاسم الكامل') }}</label>
                            <input type="text" wire:model="name" class="w-full rounded-xl border-blue-200 focus:border-blue-500 focus:ring-blue-500 font-bold text-blue-900" placeholder="{{ __('اسم المسوق') }}">
                            @error('name') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-2">{{ __('البريد الإلكتروني') }}</label>
                            <input type="email" wire:model="email" class="w-full rounded-xl border-blue-200 focus:border-blue-500 focus:ring-blue-500 font-bold text-blue-900" placeholder="email@example.com">
                            @error('email') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-blue-900 mb-2">{{ __('كلمة المرور') }} <span class="text-xs text-gray-400 font-normal">{{ $userId ? __('(اتركها فارغة لعدم التغيير)') : '' }}</span></label>
                            <input type="password" wire:model="password" class="w-full rounded-xl border-blue-200 focus:border-blue-500 focus:ring-blue-500 font-bold text-blue-900" placeholder="********">
                            @error('password') <span class="text-xs text-red-500 font-bold mt-1">{{ $message }}</span> @enderror
                        </div>

                        <button type="submit" class="w-full py-4 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-bold transition-all transform active:scale-95 shadow-lg shadow-blue-200 hover:shadow-blue-300">
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