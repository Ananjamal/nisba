<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    protected $fillable = [
        'user_id',
        'clicks_count',
        'active_clients_count',
        'total_contracts_value',
        'pending_commissions',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
