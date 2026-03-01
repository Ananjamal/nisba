<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\City;
use App\Models\Area;
use Illuminate\Support\Facades\Auth;

class CityAreaSelector extends Component
{
    public $selectedCity;
    public $selectedArea;
    public $cities;
    public $areas;
    public $name;
    public $requireAuth = false;
    public $disabled = false;

    public function mount($name = 'location', $selectedCityId = null, $selectedAreaId = null, $requireAuth = false)
    {
        $this->name = $name;
        $this->requireAuth = $requireAuth;

        // Check if authentication is required and user is not logged in
        if ($this->requireAuth && !Auth::check()) {
            $this->disabled = true;
            $this->cities = collect();
            $this->areas = collect();
            return;
        }

        $this->selectedCity = $selectedCityId;
        $this->selectedArea = $selectedAreaId;
        $this->loadCities();
        $this->loadAreas();
    }

    private function loadCities()
    {
        if ($this->disabled) return;

        $this->cities = City::active()->orderBy('name')->get();
    }

    private function loadAreas()
    {
        if ($this->disabled) return;

        $this->areas = $this->selectedCity ?
            Area::where('city_id', $this->selectedCity)->active()->orderBy('name')->get() :
            collect();
    }

    public function updatedSelectedCity($value)
    {
        if ($this->disabled) return;

        $this->selectedArea = null;
        $this->loadAreas();

        $city = $value ? City::find($value) : null;

        $this->dispatch('city-changed', [
            'cityId' => $value,
            'cityName' => $city?->name,
        ]);

        // Log the selection if user is authenticated
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->withProperties(['city_id' => $value, 'city_name' => $city?->name])
                ->log('City selected in ' . $this->name);
        }
    }

    public function updatedSelectedArea($value)
    {
        if ($this->disabled) return;

        $area = $value ? Area::find($value) : null;

        $this->dispatch('area-changed', [
            'areaId' => $value,
            'areaName' => $area?->name,
            'cityId' => $this->selectedCity,
        ]);

        // Log the selection if user is authenticated
        if (Auth::check()) {
            activity()
                ->causedBy(Auth::user())
                ->withProperties([
                    'area_id' => $value,
                    'area_name' => $area?->name,
                    'city_id' => $this->selectedCity
                ])
                ->log('Area selected in ' . $this->name);
        }
    }

    public function refreshCities()
    {
        $this->loadCities();
        $this->loadAreas();
    }

    public function getSelectedCityNameProperty()
    {
        return $this->selectedCity ? City::find($this->selectedCity)?->name : null;
    }

    public function getSelectedAreaNameProperty()
    {
        return $this->selectedArea ? Area::find($this->selectedArea)?->name : null;
    }

    public function render()
    {
        return view('livewire.components.city-area-selector');
    }
}
