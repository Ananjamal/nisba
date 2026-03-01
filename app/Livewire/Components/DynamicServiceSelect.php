<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Service;

class DynamicServiceSelect extends Component
{
    public $services;
    public $categories;
    public $selectedServices = [];
    public $selectedCategory = null;
    public $searchTerm = '';
    public $showDropdown = false;
    public $maxSelection = 5;
    public $placeholder = 'ابحث واختر الخدمات...';
    public $displayMode = 'dropdown'; // dropdown, tags, cards

    protected $listeners = [
        'servicesUpdated' => 'updateServices',
        'clearServices' => 'clearServices',
    ];

    public function mount($maxSelection = 5, $placeholder = 'ابحث واختر الخدمات...', $displayMode = 'dropdown')
    {
        $this->maxSelection = $maxSelection;
        $this->placeholder = $placeholder;
        $this->displayMode = $displayMode;
        
        $this->loadServices();
        $this->loadCategories();
    }

    public function loadServices()
    {
        $query = Service::active()->ordered();
        
        if ($this->selectedCategory) {
            $query->where('category', $this->selectedCategory);
        }
        
        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                  ->orWhere('description', 'like', '%' . $this->searchTerm . '%');
            });
        }
        
        $this->services = $query->get();
    }

    public function loadCategories()
    {
        $this->categories = Service::active()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->sort()
            ->values();
    }

    public function updatedSelectedCategory()
    {
        $this->searchTerm = '';
        $this->loadServices();
    }

    public function updatedSearchTerm()
    {
        $this->showDropdown = true;
        $this->loadServices();
    }

    public function selectService($serviceId)
    {
        if (count($this->selectedServices) >= $this->maxSelection) {
            $this->dispatch('show-message', "يمكنك اختيار {$this->maxSelection} خدمات كحد أقصى", 'error');
            return;
        }

        if (!in_array($serviceId, $this->selectedServices)) {
            $this->selectedServices[] = $serviceId;
            $this->dispatch('serviceSelected', serviceId: $serviceId);
            $this->dispatch('servicesChanged', services: $this->selectedServices);
        }
        
        $this->searchTerm = '';
        $this->showDropdown = false;
    }

    public function removeService($serviceId)
    {
        $this->selectedServices = array_filter($this->selectedServices, function ($id) use ($serviceId) {
            return $id != $serviceId;
        });
        
        $this->selectedServices = array_values($this->selectedServices);
        $this->dispatch('serviceRemoved', serviceId: $serviceId);
        $this->dispatch('servicesChanged', services: $this->selectedServices);
    }

    public function updateServices($services)
    {
        $this->selectedServices = $services;
    }

    public function clearServices()
    {
        $this->selectedServices = [];
        $this->searchTerm = '';
        $this->selectedCategory = null;
        $this->loadServices();
    }

    public function getSelectedServicesData()
    {
        return Service::whereIn('id', $this->selectedServices)->get();
    }

    public function getFilteredServices()
    {
        return $this->services->filter(function ($service) {
            return !in_array($service->id, $this->selectedServices);
        });
    }

    public function toggleDropdown()
    {
        $this->showDropdown = !$this->showDropdown;
    }

    public function closeDropdown()
    {
        $this->showDropdown = false;
    }

    public function render()
    {
        return view('livewire.components.dynamic-service-select', [
            'filteredServices' => $this->getFilteredServices(),
            'selectedServicesData' => $this->getSelectedServicesData(),
        ]);
    }
}
