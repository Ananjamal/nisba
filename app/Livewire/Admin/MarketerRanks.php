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

    public function updateRank($userId, $rankName)
    {
        $user = User::findOrFail($userId);
        $oldRank = $user->rank;
        $rankConfig = \App\Models\Rank::where('name', $rankName)->first();

        if ($rankConfig) {
            $user->update([
                'rank' => $rankName,
                'commission_multiplier' => $rankConfig->commission_multiplier
            ]);

            \App\Models\RankHistory::create([
                'user_id' => $user->id,
                'old_rank' => $oldRank,
                'new_rank' => $rankName,
                'reason' => 'تحديث يدوي من قبل المسؤول',
            ]);

            session()->flash('message', 'تم تحديث رتبة المسوق ' . $user->name . ' بنجاح');
        }
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
            'marketers' => $marketers,
            'allRanks' => \App\Models\Rank::all()
        ])->layout('layouts.admin');
    }
}
