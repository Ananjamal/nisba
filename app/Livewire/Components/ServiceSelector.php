<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Service;
use Illuminate\Support\Facades\Auth;

class ServiceSelector extends Component
{
    public $selectedServices = [];
    public $availableServices;
    public $searchTerm = '';
    public $showDropdown = false;
    public $name;
    public $multiple = true;
    public $placeholder = 'اختر الخدمات...';
    public $maxSelection = null;
    public $showCategories = true;
    public $categories = [];
    public $selectedCategory = null;
    public $recentServices = [];
    public $popularServices = [];

    public function mount($name = 'services', $selectedIds = [], $multiple = true, $maxSelection = null)
    {
        $this->name = $name;
        $this->multiple = $multiple;
        $this->maxSelection = $maxSelection;
        $this->selectedServices = $selectedIds;
        $this->loadAvailableServices();
        $this->loadCategories();
        $this->loadRecentAndPopular();
    }

    private function loadAvailableServices()
    {
        $query = Service::active()->ordered();

        if ($this->searchTerm) {
            $query->where('name', 'like', '%' . $this->searchTerm . '%')
                ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
        }

        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }

        $this->availableServices = $query->get();
    }

    private function loadCategories()
    {
        $this->categories = Service::active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->toArray();
    }

    private function loadRecentAndPopular()
    {
        // Load recently used services (for authenticated users)
        if (Auth::check()) {
            $this->recentServices = Service::active()
                ->whereHas('leads', function ($query) {
                    $query->where('user_id', Auth::id())
                        ->orderBy('created_at', 'desc')
                        ->limit(5);
                })
                ->get();
        }

        // Load popular services (most used)
        $this->popularServices = Service::active()
            ->withCount('leads')
            ->orderBy('leads_count', 'desc')
            ->limit(5)
            ->get();
    }

    public function updatedSearchTerm()
    {
        $this->loadAvailableServices();
        $this->showDropdown = true;
    }

    public function updatedSelectedCategory()
    {
        $this->loadAvailableServices();
        $this->showDropdown = true;
    }

    public function toggleService($serviceId)
    {
        if (!$this->multiple) {
            $this->selectedServices = [$serviceId];
        } else {
            if (in_array($serviceId, $this->selectedServices)) {
                $this->selectedServices = array_diff($this->selectedServices, [$serviceId]);
            } else {
                if ($this->maxSelection && count($this->selectedServices) >= $this->maxSelection) {
                    $this->dispatch('show-message', "يمكنك اختيار {$this->maxSelection} خدمات كحد أقصى", 'error');
                    return;
                }
                $this->selectedServices[] = $serviceId;
            }
        }

        $this->dispatch('services-changed', [
            'selectedServices' => $this->selectedServices,
            'serviceNames' => $this->selectedServiceNames,
        ]);

        // Log selection if user is authenticated
        if (Auth::check()) {
            $service = Service::find($serviceId);
            activity()
                ->causedBy(Auth::user())
                ->withProperties(['service_id' => $serviceId, 'service_name' => $service?->name])
                ->log('Service selected in ' . $this->name);
        }
    }

    public function removeService($serviceId)
    {
        $this->selectedServices = array_diff($this->selectedServices, [$serviceId]);

        $this->dispatch('services-changed', [
            'selectedServices' => $this->selectedServices,
            'serviceNames' => $this->selectedServiceNames,
        ]);
    }

    public function clearSelection()
    {
        $this->selectedServices = [];
        $this->dispatch('services-changed', [
            'selectedServices' => [],
            'serviceNames' => [],
        ]);
    }

    public function selectPopularService($serviceId)
    {
        $this->toggleService($serviceId);
        $this->showDropdown = false;
    }

    public function getSelectedServicesProperty()
    {
        return Service::whereIn('id', $this->selectedServices)->get();
    }

    public function getSelectedServiceNamesProperty()
    {
        return $this->selectedServices->pluck('name')->toArray();
    }

    public function getFilteredServicesProperty()
    {
        return $this->availableServices->filter(function ($service) {
            return !in_array($service->id, $this->selectedServices);
        });
    }

    public function getCanSelectMoreProperty()
    {
        return !$this->maxSelection || count($this->selectedServices) < $this->maxSelection;
    }

    public function render()
    {
        return view('livewire.components.service-selector');
    }
}
