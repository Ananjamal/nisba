<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\UserDeletionRequest;
use App\Models\LeadDeletionRequest;
use Illuminate\Support\Facades\Storage;
use Livewire\WithFileUploads;

class UserManagement extends Component
{
    use WithPagination, WithFileUploads, \App\Livewire\Traits\WithDynamicTable;

    public $role_filter = 'all';
    public $selectedUsers = [];
    public $profileImage;
    public $showDeletionModal = false;
    public $deletionReason = '';
    public $userToDelete = null;

    protected $paginationTheme = 'tailwind';

    public function mount()
    {
        $this->loadTablePrefs([
            'user' => true,
            'status' => true,
            'role' => true,
            'referral_code' => true,
            'actions' => true,
        ]);
    }

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'confirmUserDeletion' => 'confirmUserDeletion',
    ];

    public function getUsers()
    {
        return User::when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%')
                    ->orWhere('phone', 'like', '%' . $this->search . '%');
            });
        })
            ->when($this->status_filter !== 'all' && $this->status_filter !== '', function ($query) {
                $query->where('status', $this->status_filter);
            })
            ->when($this->role_filter !== 'all' && $this->role_filter !== '', function ($query) {
                $query->where('role', $this->role_filter);
            })
            ->with('deletionRequests')
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(10);
    }

    public function updateUserStatus($userId, $status)
    {
        $user = User::findOrFail($userId);
        $user->update(['status' => $status]);

        $user->logActivity("تم تغيير الحالة إلى: {$status} بواسطة الإدارة");

        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم تحديث حالة المستخدم بنجاح');
    }

    public function generateReferralCode($userId)
    {
        $user = User::findOrFail($userId);
        $code = User::generateReferralCode();
        $user->update(['referral_code' => $code]);

        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم توليد كود الإحالة بنجاح');
    }

    public function uploadProfileImage($userId)
    {
        $this->validate([
            'profileImage' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = User::findOrFail($userId);

        if ($user->profile_image) {
            Storage::delete('public/' . $user->profile_image);
        }

        $path = $this->profileImage->store('profile-images', 'public');
        $user->update(['profile_image' => $path]);

        $user->logActivity("تم تحديث صورة الملف الشخصي");

        $this->profileImage = null;
        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم تحديث صورة الملف الشخصي بنجاح');
    }

    public function confirmUserDeletion($userId)
    {
        $this->userToDelete = User::findOrFail($userId);
        $this->showDeletionModal = true;
    }

    public function requestUserDeletion()
    {
        $this->validate([
            'deletionReason' => 'required|string|min:10',
        ]);

        if (!$this->userToDelete) {
            return;
        }

        $deletionRequest = $this->userToDelete->requestDeletion(auth()->id(), $this->deletionReason);

        // Log the activity
        $this->userToDelete->logActivity("تم طلب حذف الحساب. السبب: {$this->deletionReason}");

        $this->showDeletionModal = false;
        $this->deletionReason = '';
        $this->userToDelete = null;

        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم إرسال طلب الحذف للموافقة');
    }

    public function approveDeletion($requestId)
    {
        $request = UserDeletionRequest::findOrFail($requestId);

        try {
            $request->approve(auth()->id());
            $this->dispatch('show-message', 'تمت الموافقة على طلب الحذف بنجاح');
            $this->dispatch('refreshComponent');
        } catch (\Exception $e) {
            $this->dispatch('show-message', 'خطأ: ' . $e->getMessage(), 'error');
        }
    }

    public function rejectDeletion($requestId)
    {
        $request = UserDeletionRequest::findOrFail($requestId);
        $request->update([
            'status' => 'rejected',
            'rejection_reason' => 'تم الرفض بواسطة الإدارة',
            'super_admin_approved_by' => auth()->id(),
            'super_admin_approved_at' => now(),
        ]);

        $this->dispatch('show-message', 'تم رفض طلب الحذف');
        $this->dispatch('refreshComponent');
    }

    public function approveLeadDeletion($requestId)
    {
        $request = LeadDeletionRequest::findOrFail($requestId);

        try {
            $request->approve(auth()->id());
            $this->dispatch('show-message', 'تمت الموافقة على طلب حذف العميل بنجاح');
            $this->dispatch('refreshComponent');
        } catch (\Exception $e) {
            $this->dispatch('show-message', 'خطأ: ' . $e->getMessage(), 'error');
        }
    }

    public function rejectLeadDeletion($requestId)
    {
        $request = LeadDeletionRequest::findOrFail($requestId);
        $request->reject(auth()->id(), 'تم الرفض بواسطة الإدارة');

        $this->dispatch('show-message', 'تم رفض طلب حذف العميل');
        $this->dispatch('refreshComponent');
    }

    public function bulkStatusUpdate($status)
    {
        if (empty($this->selectedUsers)) {
            $this->dispatch('show-message', 'يرجى اختيار مستخدمين أولاً', 'error');
            return;
        }

        User::whereIn('id', $this->selectedUsers)->update(['status' => $status]);

        foreach ($this->selectedUsers as $userId) {
            $user = User::find($userId);
            $user->logActivity("تم تحديث الحالة بشكل جماعي إلى: {$status}");
        }

        $this->selectedUsers = [];
        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم تحديث حالة المستخدمين المحددين بنجاح');
    }

    public function getActivityLog($userId)
    {
        $user = User::findOrFail($userId);
        return $user->activity_log ?? [];
    }

    public function getStatusBadgeClass($status)
    {
        return match ($status) {
            'active' => 'bg-green-100 text-green-800 border-green-200',
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'suspended' => 'bg-red-100 text-red-800 border-red-200',
            'inactive' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getStatusLabel($status)
    {
        return match ($status) {
            'active' => 'نشط',
            'pending' => 'في انتظار التفعيل',
            'suspended' => 'موقوف',
            'inactive' => 'خامل',
            default => $status,
        };
    }

    public function getRoleLabel($role)
    {
        return match ($role) {
            'admin' => 'مدير',
            'affiliate' => 'مسوق',
            'super-admin' => 'مدير عام',
            default => $role,
        };
    }

    public function render()
    {
        return view('livewire.admin.user-management', [
            'users' => $this->getUsers(),
            'deletionRequests' => UserDeletionRequest::with('user', 'requestedBy')
                ->where('status', 'pending')
                ->latest()
                ->get(),
            'leadDeletionRequests' => LeadDeletionRequest::with('lead', 'requestedBy')
                ->where('status', 'pending')
                ->latest()
                ->get(),
        ])->layout('layouts.admin');
    }
}
