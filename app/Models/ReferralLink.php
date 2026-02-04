<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReferralLink extends Model
{
    protected $fillable = [
        'service_name',
        'base_url',
        'logo_url',
    ];

    public function userReferrals()
    {
        return $this->hasMany(UserReferral::class);
    }
}
