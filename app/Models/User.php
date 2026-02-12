<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, \Spatie\Permission\Traits\HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'rank',
        'commission_multiplier',
        'status',
        'parent_id',
        'iban',
        'bank_name',
        'account_holder_name',
        'sector',
        'otp_code',
        'otp_expires_at',
        'promotion_plan',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'bank_account_verified_at' => 'datetime',
            'otp_expires_at' => 'datetime',
            'promotion_plan' => 'array',
        ];
    }

    public function stats()
    {
        return $this->hasOne(UserStat::class);
    }

    public function leads()
    {
        return $this->belongsToMany(Lead::class, 'lead_user')->withTimestamps();
    }

    public function referrals()
    {
        return $this->hasMany(UserReferral::class);
    }

    public function withdrawalRequests()
    {
        return $this->hasMany(WithdrawalRequest::class);
    }

    public function rankHistories()
    {
        return $this->hasMany(RankHistory::class);
    }

    public function isAdmin()
    {
        return $this->hasRole('admin') || $this->hasRole('super-admin');
    }

    public function isAffiliate()
    {
        return $this->hasRole('affiliate');
    }

    public function getRankBadgeColor()
    {
        $rankConfig = Rank::where('name', $this->rank)->first();
        return $rankConfig?->color ?? 'bg-gray-100 text-gray-700 border-gray-200';
    }

    public function getRankLabel($rankName = null)
    {
        $rank = $rankName ?? $this->rank;
        return match ($rank) {
            'bronze' => 'برونزي',
            'silver' => 'فضي',
            'gold' => 'ذهبي',
            default => 'برونزي',
        };
    }

    public function getRankIcon()
    {
        $rankConfig = Rank::where('name', $this->rank)->first();
        return $rankConfig?->icon ?? '🥉';
    }

    public function checkRankUpgrade()
    {
        if ($this->role !== 'affiliate') return;

        $stats = $this->stats;
        if (!$stats) return;

        $totalSales = $this->leads()->where('leads.status', 'sold')->count();
        $totalRevenue = $this->leads()->where('leads.status', 'sold')->sum('expected_deal_value');

        $nextRank = Rank::where(function ($query) use ($totalSales, $totalRevenue) {
            $query->where('min_sales_count', '<=', $totalSales)
                ->where('min_revenue', '<=', $totalRevenue);
        })
            ->orderByDesc('min_sales_count')
            ->orderByDesc('min_revenue')
            ->first();

        if ($nextRank && $nextRank->name !== $this->rank) {
            $oldRank = $this->rank;
            $this->update([
                'rank' => $nextRank->name,
                'commission_multiplier' => $nextRank->commission_multiplier
            ]);

            RankHistory::create([
                'user_id' => $this->id,
                'old_rank' => $oldRank,
                'new_rank' => $nextRank->name,
                'reason' => 'ترقية تلقائية بناءً على الأداء (مبيعات: ' . $totalSales . '، قيمة: ' . $totalRevenue . ')',
            ]);
        }
    }
}
