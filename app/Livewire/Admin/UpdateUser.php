<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateUser extends Component
{
    public $userId;
    public $name;
    public $email;
    public $phone;
    public $role;
    public $statusToken; // Using a different name to avoid potential conflicts with internal status

    public function mount($userId)
    {
        $user = User::findOrFail($userId);
        $this->userId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone;
        $this->role = $user->role;
        $this->statusToken = $user->status;
    }

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->userId)],
            'phone' => 'nullable|string|max:20',
            'role' => 'required|in:admin,affiliate,super-admin',
            'statusToken' => 'required|in:active,inactive,suspended,pending',
        ];
    }

    protected $validationAttributes = [
        'name' => 'الاسم',
        'email' => 'البريد الإلكتروني',
        'phone' => 'رقم الهاتف',
        'role' => 'الدور',
        'statusToken' => 'الحالة',
    ];

    public function save()
    {
        $this->validate();

        $user = User::findOrFail($this->userId);

        $oldRole = $user->role;

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'status' => $this->statusToken,
        ]);

        if ($oldRole !== $this->role) {
            $user->syncRoles([$this->role]);
        }

        $this->dispatch('close-modal');
        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم تحديث بيانات المستخدم بنجاح');
    }

    public function render()
    {
        return view('livewire.admin.update-user');
    }
}
