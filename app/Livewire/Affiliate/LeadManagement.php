<?php

namespace App\Livewire\Affiliate;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Lead;
use App\Models\Service;
use App\Models\City;
use App\Models\Area;
use Illuminate\Support\Facades\Auth;

class LeadManagement extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = 'all';
    public $cityFilter = '';
    public $areaFilter = '';
    public $selectedServices = [];
    public $showDuplicateModal = false;
    public $currentLead = null;
    public $duplicateLeads = [];
    public $approvalNotes = '';

    protected $paginationTheme = 'tailwind';
    protected $listeners = ['refreshComponent' => '$refresh'];

    // Form fields
    public $client_name;
    public $company_name;
    public $client_phone;
    public $email;
    public $city_id;
    public $area_id;
    public $notes;
    public $expected_deal_value;
    public $subscription_renewal_date;

    protected $rules = [
        'client_name' => 'required|string|max:255',
        'client_phone' => 'required|string|max:20',
        'email' => 'nullable|email|max:255',
        'company_name' => 'nullable|string|max:255',
        'city_id' => 'required|exists:cities,id',
        'area_id' => 'required|exists:areas,id',
        'selectedServices' => 'required|array|min:1',
        'selectedServices.*' => 'exists:services,id',
        'notes' => 'nullable|string|max:1000',
        'expected_deal_value' => 'nullable|numeric|min:0',
        'subscription_renewal_date' => 'nullable|date|after:today',
    ];

    public function getLeads()
    {
        return Lead::where(function ($query) {
            if (Auth::user()->isAffiliate()) {
                $query->whereHas('users', function ($q) {
                    $q->where('user_id', Auth::id());
                });
            }
        })
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('client_name', 'like', '%' . $this->search . '%')
                        ->orWhere('company_name', 'like', '%' . $this->search . '%')
                        ->orWhere('client_phone', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%')
                        ->orWhere('unique_id', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->statusFilter !== 'all', function ($query) {
                $query->where('status', $this->statusFilter);
            })
            ->when($this->cityFilter, function ($query) {
                $query->where('city', $this->cityFilter);
            })
            ->with(['users', 'services', 'approvedBy'])
            ->latest()
            ->paginate(10);
    }

    public function getCities()
    {
        return City::active()->orderBy('name')->get();
    }

    public function getAreas()
    {
        if (!$this->city_id) {
            return collect();
        }

        return Area::where('city_id', $this->city_id)->active()->orderBy('name')->get();
    }

    public function getServices()
    {
        return Service::active()->ordered()->get();
    }

    public function updatedCityId()
    {
        $this->area_id = '';
        $this->areaFilter = '';
    }

    public function createLead()
    {
        $this->validate();

        $lead = Lead::create([
            'client_name' => $this->client_name,
            'company_name' => $this->company_name,
            'client_phone' => $this->client_phone,
            'email' => $this->email,
            'city' => City::find($this->city_id)->name,
            'notes' => $this->notes,
            'expected_deal_value' => $this->expected_deal_value,
            'subscription_renewal_date' => $this->subscription_renewal_date,
            'unique_id' => Lead::generateUniqueId(),
            'status' => Lead::STATUS_NEW,
        ]);

        // Attach services
        $lead->services()->attach($this->selectedServices);

        // Attach to current user if affiliate
        if (Auth::user()->isAffiliate()) {
            $lead->users()->attach(Auth::id());
        }

        // Check for duplicates
        $duplicates = $lead->checkForDuplicates();

        if ($duplicates->isNotEmpty()) {
            $this->currentLead = $lead;
            $this->duplicateLeads = $duplicates;
            $this->showDuplicateModal = true;
        } else {
            $this->resetForm();
            $this->dispatch('refreshComponent');
            $this->dispatch('show-message', 'تم إضافة العميل بنجاح');
        }
    }

    public function approveDuplicateLead()
    {
        if (!$this->currentLead) {
            return;
        }

        $this->validate([
            'approvalNotes' => 'required|string|min:5',
        ]);

        $this->currentLead->approve(Auth::id());
        $this->currentLead->update(['notes' => $this->approvalNotes]);

        $this->showDuplicateModal = false;
        $this->resetForm();
        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', ' تم اعتماد العميل بنجاح');
    }

    public function rejectDuplicateLead()
    {
        if (!$this->currentLead) {
            return;
        }

        $this->validate([
            'approvalNotes' => 'required|string|min:5',
        ]);

        $this->currentLead->reject(Auth::id(), $this->approvalNotes);
        $this->currentLead->delete();

        $this->showDuplicateModal = false;
        $this->resetForm();
        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم رفض العميل');
    }

    public function renewSubscription($leadId, $renewalDate)
    {
        $lead = Lead::findOrFail($leadId);
        $lead->renewSubscription($renewalDate);

        $this->dispatch('refreshComponent');
        $this->dispatch('show-message', 'تم تحديث تاريخ التجديد بنجاح');
    }

    public function getStatusBadgeClass($status)
    {
        return Lead::statusBadgeClass($status);
    }

    public function getStatusLabel($status)
    {
        return Lead::statusLabel($status);
    }

    public function isNearRenewal($lead)
    {
        return $lead->isNearRenewal();
    }

    private function resetForm()
    {
        $this->reset([
            'client_name',
            'company_name',
            'client_phone',
            'email',
            'city_id',
            'area_id',
            'notes',
            'expected_deal_value',
            'subscription_renewal_date',
            'selectedServices',
            'approvalNotes'
        ]);
        $this->currentLead = null;
        $this->duplicateLeads = [];
    }

    public function render()
    {
        return view('livewire.affiliate.lead-management', [
            'leads' => $this->getLeads(),
            'cities' => $this->getCities(),
            'areas' => $this->getAreas(),
            'services' => $this->getServices(),
        ]);
    }
}
