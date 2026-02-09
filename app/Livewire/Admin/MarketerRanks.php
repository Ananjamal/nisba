<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class MarketerRanks extends Component
{
    use WithPagination;

    public $search = '';
    public $rank_filter = '';

    protected $updatesQueryString = ['search', 'rank_filter'];

    public function updateRank($userId, $rank)
    {
        $user = User::findOrFail($userId);
        $user->update(['rank' => $rank]);

        // Auto-set default multipliers based on rank if not manually overridden
        $this->updateMultiplier($userId, match ($rank) {
            'bronze' => 1.00,
            'silver' => 1.20,
            'gold' => 1.50,
            default => 1.00
        });

        session()->flash('message', 'تم تحديث رتبة المسوق ' . $user->name . ' بنجاح');
    }

    public function updateMultiplier($userId, $multiplier)
    {
        $user = User::findOrFail($userId);
        $user->update(['commission_multiplier' => $multiplier]);
        session()->flash('message', 'تم تحديث مضاعف العمولة للمسوق ' . $user->name . ' بنجاح');
    }

    public function render()
    {
        $marketers = User::where('role', 'affiliate')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->rank_filter, function ($query) {
                $query->where('rank', $this->rank_filter);
            })
            ->paginate(15);

        return view('livewire.admin.marketer-ranks', [
            'marketers' => $marketers
        ])->layout('layouts.admin');
    }
}
