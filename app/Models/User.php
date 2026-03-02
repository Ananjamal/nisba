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
        'profile_image',
        'referral_code',
        'activity_log',
        'password_changed_at',
        'password_expiry',
        'must_change_password',
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
            'activity_log' => 'array',
            'password_changed_at' => 'datetime',
            'password_expiry' => 'datetime',
            'must_change_password' => 'boolean',
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

    public function deletionRequests()
    {
        return $this->hasMany(UserDeletionRequest::class);
    }

    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class, 'causer_id');
    }

    public function referralLink()
    {
        return $this->hasOne(ReferralLink::class);
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

    public function getStatusLabel()
    {
        return match ($this->status) {
            'active' => 'نشيط',
            'inactive' => 'خامل',
            'blocked' => 'محظور',
            'pending' => 'قيد الانتظار',
            default => $this->status,
        };
    }

    public function getStatusBadgeColor()
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-800 border-green-200',
            'inactive' => 'bg-gray-100 text-gray-800 border-gray-200',
            'blocked' => 'bg-red-100 text-red-800 border-red-200',
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            default => 'bg-blue-100 text-blue-800 border-blue-200',
        };
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

    public function logActivity($description, $type = 'updated', $properties = [])
    {
        // Log to JSON column (existing)
        $log = $this->activity_log ?? [];
        $log[] = [
            'activity' => $description,
            'type' => $type,
            'timestamp' => now()->toISOString(),
            'ip' => request()->ip(),
        ];
        $this->update(['activity_log' => $log]);

        // Log to ActivityLog model (new unified system)
        return ActivityLog::log($this, $type, $description, $properties);
    }

    public static function generateReferralCode()
    {
        do {
            $code = strtoupper(substr(md5(uniqid()), 0, 8));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

    public function getProfileImageUrlAttribute()
    {
        if (!$this->profile_image) {
            return 'https://ui-avatars.com/api/?name=' . urlencode($this->name) . '&color=7F9CF5&background=EBF4FF';
        }

        return asset('storage/' . $this->profile_image);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function requestDeletion($requestedBy, $reason = null)
    {
        return $this->deletionRequests()->create([
            'requested_by' => $requestedBy,
            'reason' => $reason,
            'status' => 'pending',
            'current_approval_level' => 'manager',
        ]);
    }

    public function isPasswordExpired()
    {
        return $this->password_expiry && $this->password_expiry->isPast();
    }

    public function mustChangePassword()
    {
        return $this->must_change_password || $this->isPasswordExpired();
    }

    public function getPasswordStrengthAttribute()
    {
        if (!$this->password) return 0;

        $strength = 0;
        $password = ''; // We can't check the hashed password directly

        // This is a placeholder - in practice, you'd need to store password strength when password is set
        return $strength;
    }

    public function forcePasswordChange()
    {
        $this->update([
            'must_change_password' => true,
            'password_changed_at' => null,
        ]);
    }

    public function clearPasswordChangeRequirement()
    {
        $this->update([
            'must_change_password' => false,
            'password_changed_at' => now(),
            'password_expiry' => now()->addDays(90), // Password expires in 90 days
        ]);
    }
}
