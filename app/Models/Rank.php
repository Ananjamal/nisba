<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    protected $fillable = [
        'name',
        'min_sales_count',
        'min_revenue',
        'commission_multiplier',
        'icon',
        'color',
        'description',
    ];

    public function getLabelAttribute()
    {
        return match ($this->name) {
            'bronze' => 'برونزي',
            'silver' => 'فضي',
            'gold' => 'ذهبي',
            default => ucfirst($this->name),
        };
    }
}
