<?php

namespace App\Livewire\Affiliate;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\WithdrawalRequest;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class WithdrawalManagement extends Component
{
    use WithPagination;

    public $amount;
    public $notes;
    public $showRequestModal = false;
    public $showDelegationModal = false;
    public $selectedWithdrawal = null;
    public $delegateToUserId;
    public $delegationNotes;

    protected $paginationTheme = 'tailwind';
    protected $listeners = ['refreshComponent' => '$refresh'];

    protected $rules = [
        'amount' => 'required|numeric|min:100|max:10000',
        'notes' => 'nullable|string|max:500',
    ];

    public function getWithdrawalRequests()
    {
        return WithdrawalRequest::where('user_id', Auth::id())
            ->with(['lead', 'delegatedTo', 'adminApprover', 'financeApprover'])
            ->latest()
            ->paginate(10);
    }

    public function getAvailableUsers()
    {
        return User::where('id', '!=', Auth::id())
            ->where('role', 'admin')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    public function getSystemSettings()
    {
        return [
            'tax_rate' => SystemSetting::get('tax_rate', 15),
            'min_amount' => SystemSetting::get('min_withdrawal_amount', 100),
            'max_amount' => SystemSetting::get('max_withdrawal_amount', 10000),
        ];
    }

    public function calculateTaxAmount()
    {
        if (!$this->amount) {
            return 0;
        }

        $taxRate = SystemSetting::get('tax_rate', 15);
        return ($this->amount * $taxRate) / 100;
    }

    public function calculateFinalAmount()
    {
        if (!$this->amount) {
            return 0;
        }

        return $this->amount - $this->calculateTaxAmount();
    }

    public function createWithdrawalRequest()
    {
        $this->validate();

        $settings = $this->getSystemSettings();

        // Update validation rules based on settings
        $this->validate([
            'amount' => 'required|numeric|min:' . $settings['min_amount'] . '|max:' . $settings['max_amount'],
        ]);

        $withdrawal = WithdrawalRequest::create([
            'user_id' => Auth::id(),
            'amount' => $this->amount,
            'tax_rate' => $settings['tax_rate'],
            'tax_amount' => $this->calculateTaxAmount(),
            'final_amount' => $this->calculateFinalAmount(),
            'notes' => $this->notes,
            'status' => 'pending',
        ]);

        // Auto-calculate tax is handled by model's boot method
        $withdrawal->calculateTax();

        $this->reset(['amount', 'notes', 'showRequestModal']);
        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم إرسال طلب السحب بنجاح');
    }

    public function requestDelegation($withdrawalId)
    {
        $this->selectedWithdrawal = WithdrawalRequest::findOrFail($withdrawalId);
        $this->showDelegationModal = true;
    }

    public function submitDelegation()
    {
        $this->validate([
            'delegateToUserId' => 'required|exists:users,id',
            'delegationNotes' => 'required|string|min:5',
        ]);

        if (!$this->selectedWithdrawal) {
            return;
        }

        $this->selectedWithdrawal->delegate($this->delegateToUserId);
        $this->selectedWithdrawal->update(['notes' => $this->delegationNotes]);

        // Log the delegation
        /** @var User $user */
        $user = Auth::user();
        if (method_exists($user, 'logActivity')) {
            $user->logActivity("تم تفويض طلب السحب رقم {$this->selectedWithdrawal->id} إلى المستخدم {$this->delegateToUserId}");
        }

        $this->showDelegationModal = false;
        $this->reset(['delegateToUserId', 'delegationNotes', 'selectedWithdrawal']);
        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم إرسال طلب التفويض بنجاح');
    }

    public function cancelWithdrawalRequest($withdrawalId)
    {
        $withdrawal = WithdrawalRequest::findOrFail($withdrawalId);

        if ($withdrawal->user_id !== Auth::id() || $withdrawal->status !== 'pending') {
            $this->dispatch('show-message', 'لا يمكن إلغاء هذا الطلب', 'error');
            return;
        }

        $withdrawal->update(['status' => 'cancelled']);

        /** @var User $user */
        $user = Auth::user();
        if (method_exists($user, 'logActivity')) {
            $user->logActivity("تم إلغاء طلب السحب رقم {$withdrawal->id}");
        }

        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم إلغاء طلب السحب بنجاح');
    }

    public function getStatusBadgeClass($status)
    {
        return match ($status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'approved' => 'bg-green-100 text-green-800 border-green-200',
            'rejected' => 'bg-red-100 text-red-800 border-red-200',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getStatusLabel($status)
    {
        return match ($status) {
            'pending' => 'قيد الانتظار',
            'approved' => 'معتمد',
            'rejected' => 'مرفوض',
            'cancelled' => 'ملغي',
            default => $status,
        };
    }

    public function render()
    {
        return view('livewire.affiliate.withdrawal-management', [
            'withdrawalRequests' => $this->getWithdrawalRequests(),
            'availableUsers' => $this->getAvailableUsers(),
            'settings' => $this->getSystemSettings(),
        ]);
    }
}
