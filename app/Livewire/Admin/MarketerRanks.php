<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class MarketerRanks extends Component
{
    use WithPagination;
    use \App\Livewire\Traits\WithDynamicTable;

    public $search = '';
    public $rank_filter = '';
    public $sector_filter = '';
    public $showViewModal = false;
    public $viewUser = null;

    public function mount()
    {
        $this->sortField = 'name';
        $this->sortDirection = 'asc';
    }

    protected $updatesQueryString = ['search', 'rank_filter', 'sector_filter', 'sortField', 'sortDirection'];

    public function exportExcel()
    {
        return redirect()->route('admin.reports.marketers.ranks.excel', [
            'search' => $this->search,
            'rank' => $this->rank_filter,
            'sector' => $this->sector_filter,
        ]);
    }

    public function exportPdf()
    {
        return redirect()->route('admin.reports.marketers.ranks.pdf', [
            'search' => $this->search,
            'rank' => $this->rank_filter,
            'sector' => $this->sector_filter,
        ]);
    }

    public function openViewModal($id)
    {
        $this->viewUser = User::withCount('leads')->findOrFail($id);
        $this->showViewModal = true;
    }

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
            ->withCount('leads')
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->rank_filter, function ($query) {
                $query->where('rank', $this->rank_filter);
            })
            ->when($this->sector_filter, function ($query) {
                $query->where('sector', $this->sector_filter);
            })
            ->orderBy($this->sortField, $this->sortDirection)
            ->paginate(15);

        return view('livewire.admin.marketer-ranks', [
            'marketers' => $marketers,
            'allRanks' => \App\Models\Rank::all()
        ])->layout('layouts.admin');
    }
}
