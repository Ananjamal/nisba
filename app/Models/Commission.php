<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = [
        'lead_id',
        'amount',
        'status',
        'paid_at',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
