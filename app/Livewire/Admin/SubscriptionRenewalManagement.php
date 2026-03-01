<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lead;
use App\Models\SubscriptionRenewal;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class SubscriptionRenewalManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $renewalTypeFilter = 'all';
    public $dateRangeFilter = 'all'; // upcoming, expired, this_month, next_month
    public $selectedRenewals = [];
    public $selectAll = false;
    public $showRenewalModal = false;
    public $showBulkRenewalModal = false;
    public $showCreateModal = false;
    public $selectedRenewal = null;
    public $renewalNotes = '';
    public $renewalAmount = '';
    public $newExpiryDate = '';
    public $bulkRenewalDays = 30;
    public $bulkRenewalAmount = '';

    public $createLeadId = '';
    public $createRenewalDate = '';
    public $createRenewalAmount = '';
    public $createRenewalNotes = '';

    // Professional table properties
    public $columns = [
        'client' => true,
        'renewal_date' => true,
        'amount' => true,
        'status' => true,
        'marketer' => true,
        'actions' => true,
    ];
    public $sortField = 'renewal_date';
    public $sortDirection = 'desc';

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshComponent' => '$refresh',
        'openRenewalModal' => 'openRenewalModal',
    ];

    public function getRenewals()
    {
        $query = SubscriptionRenewal::with(['lead', 'lead.users', 'renewedBy']);

        // Search by client name or company
        if ($this->search) {
            $query->whereHas('lead', function ($q) {
                $q->where('client_name', 'like', '%' . $this->search . '%')
                    ->orWhere('company_name', 'like', '%' . $this->search . '%');
            });
        }

        // Status filter
        if ($this->statusFilter !== 'all') {
            $query->where('status', $this->statusFilter);
        }

        // Renewal type filter
        if ($this->renewalTypeFilter !== 'all') {
            $query->where('renewal_type', $this->renewalTypeFilter);
        }

        // Date range filter
        match ($this->dateRangeFilter) {
            'upcoming' => $query->upcoming(30),
            'expired' => $query->expired(),
            'this_month' => $query->whereMonth('renewal_date', now()->month)
                ->whereYear('renewal_date', now()->year),
            'next_month' => $query->whereMonth('renewal_date', now()->addMonth()->month)
                ->whereYear('renewal_date', now()->addMonth()->year),
            default => null,
        };

        // Sorting
        if ($this->sortField) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            $query->latest('renewal_date');
        }

        return $query->paginate(15);
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getStatistics()
    {
        $total = SubscriptionRenewal::count();
        $pending = SubscriptionRenewal::where('status', 'pending')->count();
        $completed = SubscriptionRenewal::where('status', 'completed')->count();
        $upcoming = SubscriptionRenewal::upcoming(30)->count();
        $expired = SubscriptionRenewal::expired()->count();

        return [
            'total' => $total,
            'pending' => $pending,
            'completed' => $completed,
            'upcoming' => $upcoming,
            'expired' => $expired,
        ];
    }

    public function openRenewalModal($renewalId)
    {
        $this->selectedRenewal = SubscriptionRenewal::with('lead')->find($renewalId);
        $this->renewalAmount = $this->selectedRenewal->renewal_amount;
        $currentExpiry = $this->selectedRenewal?->lead?->subscription_renewal_date;
        $this->newExpiryDate = $currentExpiry
            ? $currentExpiry->copy()->addYear()->format('Y-m-d')
            : now()->addYear()->format('Y-m-d');
        $this->renewalNotes = '';
        $this->showRenewalModal = true;
    }

    public function updatedSelectAll($value)
    {
        if ($value) {
            $this->selectedRenewals = $this->getRenewals()->pluck('id')->toArray();
        } else {
            $this->selectedRenewals = [];
        }
    }

    public function openCreateRenewalModal()
    {
        $this->resetValidation();
        $this->createLeadId = '';
        $this->createRenewalAmount = '';
        $this->createRenewalNotes = '';
        $this->createRenewalDate = now()->addMonthNoOverflow()->format('Y-m-d');
        $this->showCreateModal = true;
    }

    public function updatedCreateLeadId($value)
    {
        $lead = $value ? Lead::find($value) : null;
        if ($lead && $lead->subscription_renewal_date) {
            $this->createRenewalDate = $lead->subscription_renewal_date->format('Y-m-d');
            if ($this->createRenewalAmount === '' && $lead->subscription_amount) {
                $this->createRenewalAmount = $lead->subscription_amount;
            }
        }
    }

    public function createRenewal()
    {
        $this->validate([
            'createLeadId' => 'required|exists:leads,id',
            'createRenewalDate' => 'required|date',
            'createRenewalAmount' => 'nullable|numeric|min:0',
            'createRenewalNotes' => 'nullable|string|max:500',
        ]);

        $lead = Lead::find($this->createLeadId);
        if (!$lead) {
            return;
        }

        SubscriptionRenewal::create([
            'lead_id' => $lead->id,
            'renewal_date' => $this->createRenewalDate,
            'previous_expiry_date' => $lead->subscription_renewal_date,
            'renewal_amount' => $this->createRenewalAmount !== '' ? $this->createRenewalAmount : $lead->subscription_amount,
            'renewal_type' => 'manual',
            'renewed_by' => auth()->id(),
            'notes' => $this->createRenewalNotes,
            'status' => 'pending',
        ]);

        $this->showCreateModal = false;
        $this->createLeadId = '';
        $this->createRenewalDate = '';
        $this->createRenewalAmount = '';
        $this->createRenewalNotes = '';

        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم إنشاء التجديد بنجاح');
    }

    public function processRenewal()
    {
        $this->validate([
            'renewalAmount' => 'required|numeric|min:0',
            'newExpiryDate' => 'required|date|after:today',
            'renewalNotes' => 'nullable|string|max:500',
        ]);

        if (!$this->selectedRenewal) {
            return;
        }

        $this->selectedRenewal->markAsCompleted(
            $this->newExpiryDate,
            $this->renewalAmount,
            $this->renewalNotes
        );

        // Log the activity
        if ($this->selectedRenewal->lead && method_exists($this->selectedRenewal->lead, 'logActivity')) {
            $this->selectedRenewal->lead->logActivity("تم تجديد الاشتراك حتى: {$this->newExpiryDate}");
        }

        $this->showRenewalModal = false;
        $this->selectedRenewal = null;
        $this->renewalAmount = '';
        $this->newExpiryDate = '';
        $this->renewalNotes = '';

        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم تجديد الاشتراك بنجاح');
    }

    public function bulkRenewal()
    {
        $this->validate([
            'bulkRenewalDays' => 'required|integer|min:1|max:365',
            'bulkRenewalAmount' => 'required|numeric|min:0',
        ]);

        if (empty($this->selectedRenewals)) {
            $this->dispatch('show-message', 'يرجى اختيار تجديدات أولاً', 'error');
            return;
        }

        $renewedCount = 0;
        foreach ($this->selectedRenewals as $renewalId) {
            $renewal = SubscriptionRenewal::find($renewalId);
            if ($renewal && $renewal->status === 'pending') {
                $newExpiryDate = $renewal->renewal_date->addDays($this->bulkRenewalDays);
                $renewal->markAsCompleted($newExpiryDate, $this->bulkRenewalAmount);
                $renewedCount++;
            }
        }

        $this->selectedRenewals = [];
        $this->showBulkRenewalModal = false;
        $this->bulkRenewalDays = 30;
        $this->bulkRenewalAmount = '';

        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', "تم تجديد {$renewedCount} اشتراك بنجاح");
    }

    public function sendRenewalReminder($renewalId)
    {
        $renewal = SubscriptionRenewal::find($renewalId);
        if ($renewal) {
            $renewal->sendNotification();
            $this->dispatch('show-message', 'تم إرسال تذكير التجديد بنجاح');
        }
    }

    public function cancelRenewal($renewalId)
    {
        $renewal = SubscriptionRenewal::find($renewalId);
        if ($renewal) {
            $renewal->update(['status' => 'cancelled']);
            $renewal->lead->update(['subscription_status' => 'cancelled']);
            $this->dispatch('show-message', 'تم إلغاء التجديد');
        }
    }

    public function createRenewalForLead($leadId)
    {
        $lead = Lead::find($leadId);
        if (!$lead || !$lead->subscription_renewal_date) {
            return;
        }

        // Create renewal record if it doesn't exist
        $existingRenewal = SubscriptionRenewal::where('lead_id', $leadId)
            ->where('status', 'pending')
            ->first();

        if (!$existingRenewal) {
            SubscriptionRenewal::create([
                'lead_id' => $leadId,
                'renewal_date' => $lead->subscription_renewal_date,
                'previous_expiry_date' => $lead->subscription_renewal_date,
                'renewal_amount' => $lead->subscription_amount,
                'renewal_type' => 'automatic',
                'status' => 'pending',
            ]);
        }

        $this->dispatch('refreshComponent');
    }

    public function getDaysLabel($days)
    {
        if ($days > 0) {
            return "متبقي {$days} يوم";
        } elseif ($days === 0) {
            return "ينتهي اليوم";
        } else {
            return "منتهي منذ " . abs($days) . " يوم";
        }
    }

    public function getDaysBadgeClass($days)
    {
        if ($days > 30) {
            return 'bg-green-100 text-green-800 border-green-200';
        } elseif ($days > 7) {
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        } elseif ($days > 0) {
            return 'bg-orange-100 text-orange-800 border-orange-200';
        } else {
            return 'bg-red-100 text-red-800 border-red-200';
        }
    }

    public function render()
    {
        return view('livewire.admin.subscription-renewal-management', [
            'renewals' => $this->getRenewals(),
            'statistics' => $this->getStatistics(),
            'leadsForRenewal' => Lead::query()
                ->orderByRaw('subscription_renewal_date is null')
                ->latest()
                ->take(200)
                ->get(['id', 'client_name', 'company_name', 'subscription_renewal_date', 'subscription_amount']),
        ]);
    }
}
