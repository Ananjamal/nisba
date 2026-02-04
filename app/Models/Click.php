<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Click extends Model
{
    protected $fillable = [
        'user_id',
        'referral_link_id',
        'ip_address',
        'user_agent',
        'referer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referralLink()
    {
        return $this->belongsTo(ReferralLink::class);
    }
}
