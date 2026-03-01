<?php

namespace App\Livewire\Profile;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;

class UpdateProfileImageForm extends Component
{
    use WithFileUploads;

    public $profileImage;

    public function save()
    {
        $this->validate([
            'profileImage' => ['required', 'image', 'max:2048'],
        ]);

        $user = Auth::user();

        $path = $this->profileImage->store('profile_images', 'public');

        $user->update([
            'profile_image' => $path,
        ]);

        $this->profileImage = null;

        $this->dispatch('profile-updated');
    }

    public function remove()
    {
        $user = Auth::user();

        $user->update([
            'profile_image' => null,
        ]);

        $this->dispatch('profile-updated');
    }

    public function render()
    {
        return view('livewire.profile.update-profile-image-form');
    }
}
