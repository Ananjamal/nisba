<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'user_id',
        'client_name',
        'company_name',
        'city',
        'client_phone',
        'contract_id',
        'status',
        'commission_amount',
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

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }
}
