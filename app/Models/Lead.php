<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'user_id',
        'referral_link_id',
        'client_name',
        'company_name',
        'city',
        'client_phone',
        'email',
        'product_interest',
        'service_type',
        'expected_deal_value',
        'source',
        'contract_id',
        'status',
        'commission_amount',
        'commission_type',
        'commission_rate',
        'sector',
        'notes',
        'needs',
        'recommended_systems',
    ];

    protected $casts = [
        'recommended_systems' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referralLink()
    {
        return $this->belongsTo(ReferralLink::class);
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }
}
