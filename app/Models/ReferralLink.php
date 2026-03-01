<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    protected $fillable = [
        'service_name',
        'base_url',
        'logo_url',
        'link_type', // manual, auto
        'custom_code', // for manual links
        'is_active',
        'click_count',
        'conversion_count',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'click_count' => 'integer',
        'conversion_count' => 'integer',
    ];

    public function userReferrals()
    {
        return $this->hasMany(UserReferral::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_referral_links')
            ->withPivot('custom_code', 'is_active')
            ->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('link_type', $type);
    }

    public function generateUniqueCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while ($this->userReferrals()->where('unique_ref_id', $code)->exists());

        return $code;
    }

    public function createReferralForUser($userId, $customCode = null)
    {
        $uniqueRefId = $customCode ?: $this->generateUniqueCode();

        // Check if user already has a referral for this link
        $existingReferral = $this->userReferrals()
            ->where('user_id', $userId)
            ->first();

        if ($existingReferral) {
            return $existingReferral;
        }

        return $this->userReferrals()->create([
            'user_id' => $userId,
            'unique_ref_id' => $uniqueRefId,
            'referral_link_id' => $this->id,
        ]);
    }

    public function getReferralUrl($uniqueRefId)
    {
        $baseUrl = rtrim($this->base_url, '/');
        $connector = str_contains($baseUrl, '?') ? '&' : '?';

        // Special handling for different services
        if (str_contains($baseUrl, 'daftra.com')) {
            return $baseUrl . $connector . 'nisba_ref=' . $uniqueRefId;
        }

        if (str_contains($baseUrl, 'qoyod.com')) {
            return $baseUrl . $connector . 'ref=' . $uniqueRefId;
        }

        // Generic handling
        return $baseUrl . $connector . 'ref=' . $uniqueRefId;
    }

    public function incrementClickCount()
    {
        $this->increment('click_count');
    }

    public function incrementConversionCount()
    {
        $this->increment('conversion_count');
    }

    public function getConversionRate()
    {
        if ($this->click_count === 0) {
            return 0;
        }

        return ($this->conversion_count / $this->click_count) * 100;
    }

    public function getLinkTypeLabel()
    {
        return match ($this->link_type) {
            'manual' => 'يدوي',
            'auto' => 'تلقائي',
            default => $this->link_type,
        };
    }

    public function getLinkTypeBadgeClass()
    {
        return match ($this->link_type) {
            'manual' => 'bg-blue-100 text-blue-800 border-blue-200',
            'auto' => 'bg-green-100 text-green-800 border-green-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function canUserCreateCustomCode($userId)
    {
        return $this->link_type === 'manual' ||
            ($this->link_type === 'auto' && $this->users()->where('user_id', $userId)->exists());
    }

    public function validateCustomCode($code)
    {
        // Check if code follows the required format
        if (!preg_match('/^[A-Z0-9]{6,12}$/', $code)) {
            return false;
        }

        // Check if code is unique
        return !$this->userReferrals()->where('unique_ref_id', $code)->exists();
    }
}
