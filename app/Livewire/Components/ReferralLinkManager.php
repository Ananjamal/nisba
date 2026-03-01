<?php

namespace App\Livewire\Components;

use Livewire\Component;
use App\Models\ReferralLink;
use App\Models\UserReferral;

class ReferralLinkManager extends Component
{
    public $referralLinks;
    public $userReferrals;
    public $selectedLink = null;
    public $customCode = '';
    public $showCreateModal = false;
    public $showCustomCodeModal = false;
    public $linkType = 'auto';
    public $serviceName = '';
    public $baseUrl = '';
    public $logoUrl = '';
    public $userId;

    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];

    public function mount($userId = null)
    {
        $this->userId = $userId ?? (auth()->user() ? auth()->user()->id : null);
        $this->loadReferralLinks();
        $this->loadUserReferrals();
    }

    public function loadReferralLinks()
    {
        $this->referralLinks = ReferralLink::active()->orderBy('service_name')->get();
    }

    public function loadUserReferrals()
    {
        $this->userReferrals = UserReferral::where('user_id', $this->userId)
            ->with('referralLink')
            ->get();
    }

    public function createReferralLink()
    {
        $this->validate([
            'serviceName' => 'required|string|max:255',
            'baseUrl' => 'required|url',
            'linkType' => 'required|in:auto,manual',
            'logoUrl' => 'nullable|url',
        ]);

        $referralLink = ReferralLink::create([
            'service_name' => $this->serviceName,
            'base_url' => $this->baseUrl,
            'link_type' => $this->linkType,
            'logo_url' => $this->logoUrl,
        ]);

        // Auto-create referral for current user
        $referralLink->createReferralForUser($this->userId);

        $this->showCreateModal = false;
        $this->reset(['serviceName', 'baseUrl', 'logoUrl', 'linkType']);
        $this->loadReferralLinks();
        $this->loadUserReferrals();

        $this->dispatch('show-message', 'تم إنشاء رابط الإحالة بنجاح');
    }

    public function generateReferralForLink($linkId)
    {
        $referralLink = ReferralLink::find($linkId);
        if (!$referralLink) {
            return;
        }

        $referral = $referralLink->createReferralForUser($this->userId);

        $this->loadUserReferrals();
        $this->dispatch('show-message', 'تم توليد رابط الإحالة بنجاح');
    }

    public function openCustomCodeModal($linkId)
    {
        $this->selectedLink = ReferralLink::find($linkId);
        $this->customCode = '';
        $this->showCustomCodeModal = true;
    }

    public function createCustomCode()
    {
        $this->validate([
            'customCode' => 'required|string|regex:/^[A-Z0-9]{6,12}$/',
        ]);

        if (!$this->selectedLink) {
            return;
        }

        if (!$this->selectedLink->validateCustomCode($this->customCode)) {
            $this->addError('customCode', 'الكود غير صالح أو مستخدم بالفعل');
            return;
        }

        $referral = $this->selectedLink->createReferralForUser($this->userId, $this->customCode);

        $this->showCustomCodeModal = false;
        $this->selectedLink = null;
        $this->customCode = '';
        $this->loadUserReferrals();

        $this->dispatch('show-message', 'تم إنشاء الكود المخصص بنجاح');
    }

    public function copyToClipboard($text)
    {
        $this->dispatch('copyToClipboard', text: $text);
        $this->dispatch('show-message', 'تم نسخ الرابط إلى الحافظة');
    }

    public function deactivateReferral($referralId)
    {
        $referral = UserReferral::find($referralId);
        if ($referral && $referral->user_id === $this->userId) {
            $referral->update(['is_active' => false]);
            $this->loadUserReferrals();
            $this->dispatch('show-message', 'تم إلغاء تفعيل رابط الإحالة');
        }
    }

    public function activateReferral($referralId)
    {
        $referral = UserReferral::find($referralId);
        if ($referral && $referral->user_id === $this->userId) {
            $referral->update(['is_active' => true]);
            $this->loadUserReferrals();
            $this->dispatch('show-message', 'تم تفعيل رابط الإحالة');
        }
    }

    public function getReferralUrl($referral)
    {
        if (!$referral->referralLink) {
            return '#';
        }

        return $referral->referralLink->getReferralUrl($referral->unique_ref_id);
    }

    public function getStats()
    {
        $totalClicks = $this->userReferrals->sum(function ($referral) {
            return $referral->clicks()->count();
        });

        $totalConversions = $this->userReferrals->sum(function ($referral) {
            return $referral->leads()->count();
        });

        return [
            'total_referrals' => $this->userReferrals->count(),
            'active_referrals' => $this->userReferrals->where('is_active', true)->count(),
            'total_clicks' => $totalClicks,
            'total_conversions' => $totalConversions,
            'conversion_rate' => $totalClicks > 0 ? round(($totalConversions / $totalClicks) * 100, 2) : 0,
        ];
    }

    public function render()
    {
        return view('livewire.components.referral-link-manager', [
            'stats' => $this->getStats(),
        ]);
    }
}
