<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\City;
use App\Models\Area;

class DynamicLocationSelect extends Component
{
    public $cities;
    public $areas;
    public $selectedCity;
    public $selectedArea;
    public $cityLabel = 'المدينة';
    public $areaLabel = 'المنطقة';
    public $required = false;
    public $showInactive = false;

    protected $listeners = [
        'locationUpdated' => 'updateLocation',
        'resetLocation' => 'resetLocation',
    ];

    public function mount($cityLabel = 'المدينة', $areaLabel = 'المنطقة', $required = false, $showInactive = false)
    {
        $this->cityLabel = $cityLabel;
        $this->areaLabel = $areaLabel;
        $this->required = $required;
        $this->showInactive = $showInactive;
        
        $this->loadCities();
        $this->areas = collect();
    }

    public function loadCities()
    {
        $query = City::query();
        
        if (!$this->showInactive) {
            $query->active();
        }
        
        $this->cities = $query->orderBy('name')->get();
    }

    public function updatedSelectedCity($cityId)
    {
        $this->selectedArea = null;
        
        if ($cityId) {
            $query = Area::where('city_id', $cityId);
            
            if (!$this->showInactive) {
                $query->active();
            }
            
            $this->areas = $query->orderBy('name')->get();
        } else {
            $this->areas = collect();
        }

        $this->dispatch('areaUpdated', areas: $this->areas->toArray());
        $this->dispatch('locationChanged', 
            city: $this->selectedCity, 
            area: $this->selectedArea,
            cityName: $this->getCityName(),
            areaName: $this->getAreaName()
        );
    }

    public function updatedSelectedArea($areaId)
    {
        $this->dispatch('locationChanged', 
            city: $this->selectedCity, 
            area: $this->selectedArea,
            cityName: $this->getCityName(),
            areaName: $this->getAreaName()
        );
    }

    public function updateLocation($cityId, $areaId)
    {
        $this->selectedCity = $cityId;
        $this->selectedArea = $areaId;
        
        if ($cityId) {
            $query = Area::where('city_id', $cityId);
            
            if (!$this->showInactive) {
                $query->active();
            }
            
            $this->areas = $query->orderBy('name')->get();
        }
    }

    public function resetLocation()
    {
        $this->selectedCity = null;
        $this->selectedArea = null;
        $this->areas = collect();
    }

    public function getCityName()
    {
        if (!$this->selectedCity) {
            return null;
        }
        
        $city = $this->cities->find($this->selectedCity);
        return $city ? $city->name : null;
    }

    public function getAreaName()
    {
        if (!$this->selectedArea) {
            return null;
        }
        
        $area = $this->areas->find($this->selectedArea);
        return $area ? $area->name : null;
    }

    public function getLocationData()
    {
        return [
            'city_id' => $this->selectedCity,
            'area_id' => $this->selectedArea,
            'city_name' => $this->getCityName(),
            'area_name' => $this->getAreaName(),
        ];
    }

    public function render()
    {
        return view('livewire.components.dynamic-location-select');
    }
}
