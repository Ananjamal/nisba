<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;

class NotificationsDropdown extends Component
{
    public function getNotificationsProperty()
    {
        return Auth::user()->unreadNotifications()->take(5)->get();
    }

    public function getUnreadCountProperty()
    {
        return Auth::user()->unreadNotifications()->count();
    }

    public function markAsRead($notificationId)
    {
        $notification = Auth::user()->notifications()->findOrFail($notificationId);
        $notification->markAsRead();

        if (isset($notification->data['url'])) {
            return redirect($notification->data['url']);
        }
    }

    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        $this->dispatch('toast', [
            'type' => 'success',
            'message' => 'تم تحديد جميع الإشعارات كمقروءة'
        ]);
    }

    public function render()
    {
        return view('livewire.components.notifications-dropdown');
    }
}
