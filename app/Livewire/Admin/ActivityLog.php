<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityLogModal extends Component
{
    public $userId;
    public $user;
    public $activities;

    public function mount($userId)
    {
        $this->userId = $userId;
        $this->user = User::findOrFail($userId);
        $this->loadActivities();
    }

    public function loadActivities()
    {
        $this->activities = ActivityLog::where('causer_id', $this->userId)
            ->orWhere(function ($query) {
                $query->where('subject_type', User::class)
                    ->where('subject_id', $this->userId);
            })
            ->latest()
            ->limit(20)
            ->get();
    }

    public function closeModal()
    {
        $this->dispatch('close-modal', 'admin.activity-log');
    }

    public function render()
    {
        return view('livewire.admin.activity-log-modal');
    }
}
