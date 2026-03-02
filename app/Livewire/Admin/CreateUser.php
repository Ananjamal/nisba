<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CreateUser extends Component
{
    public $name;
    public $email;
    public $phone;
    public $role = 'affiliate';
    public $password;
    public $password_confirmation;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,affiliate,super-admin',
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    protected $validationAttributes = [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'role' => 'الدور',
        'password' => 'كلمة المرور',
    ];

    public function save()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'password' => Hash::make($this->password),
            'status' => 'active',
        ]);

        $user->assignRole($this->role);

        $this->dispatch('close-modal');
        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم إضافة المستخدم بنجاح');
    }

    public function render()
    {
        return view('livewire.admin.create-user');
    }
}
