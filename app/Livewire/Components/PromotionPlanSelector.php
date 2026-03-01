<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\PromotionPlan;

class PromotionPlanSelector extends Component
{
    public $selectedPlan;
    public $availablePlans;
    public $name;

    public function mount($name = 'promotion_plan', $selectedId = null)
    {
        $this->name = $name;
        $this->selectedPlan = $selectedId;
        $this->loadAvailablePlans();
    }

    public function loadAvailablePlans()
    {
        $this->availablePlans = PromotionPlan::active()->ordered()->get();
    }

    public function selectPlan($planId)
    {
        $this->selectedPlan = $planId;
        
        $this->dispatch('promotion-plan-changed', [
            'selectedPlan' => $planId,
            'plan' => $this->availablePlans->find($planId),
        ]);
    }

    public function getSelectedPlanProperty()
    {
        return $this->selectedPlan ? $this->availablePlans->find($this->selectedPlan) : null;
    }

    public function render()
    {
        return view('livewire.components.promotion-plan-selector');
    }
}
