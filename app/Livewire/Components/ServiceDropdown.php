<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\Service;

class ServiceDropdown extends Component
{
    public $services;
    public $selectedServices = [];
    public $placeholder = 'اختر الخدمات...';

    protected $listeners = [
        'serviceSelected' => 'handleServiceSelection',
        'clearService' => 'clearService',
        'clearAllServices' => 'clearAllServices',
    ];

    public function mount($placeholder = 'اختر الخدمات...')
    {
        $this->placeholder = $placeholder;
        $this->loadServices();
    }

    public function loadServices()
    {
        $this->services = Service::active()->ordered()->get();
    }

    public function selectService($serviceId)
    {
        if (!in_array($serviceId, $this->selectedServices)) {
            $this->selectedServices[] = $serviceId;
            $service = Service::find($serviceId);
            $this->dispatch('serviceChanged', serviceId: $serviceId, service: $service);
        }
    }

    public function removeService($serviceId)
    {
        $this->selectedServices = array_filter($this->selectedServices, function ($id) use ($serviceId) {
            return $id != $serviceId;
        });
        $this->selectedServices = array_values($this->selectedServices);
        $this->dispatch('serviceRemoved', serviceId: $serviceId);
    }

    public function handleServiceSelection($serviceId)
    {
        $this->selectService($serviceId);
    }

    public function clearService()
    {
        $this->selectedServices = [];
        $this->dispatch('serviceChanged', serviceId: null, service: null);
    }

    public function clearAllServices()
    {
        $this->selectedServices = [];
        $this->dispatch('allServicesCleared');
    }

    public function getSelectedServicesData()
    {
        return Service::whereIn('id', $this->selectedServices)->get();
    }

    public function render()
    {
        return view('livewire.components.service-dropdown');
    }
}
