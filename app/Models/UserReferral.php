<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserReferral extends Model
{
    protected $fillable = [
        'user_id',
        'referral_link_id',
        'unique_ref_id',
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
