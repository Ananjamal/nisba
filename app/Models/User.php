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

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isAffiliate()
    {
        return $this->role === 'affiliate';
    }

    public function getRankBadgeColor()
    {
        return match ($this->rank) {
            'bronze' => 'bg-orange-100 text-orange-700 border-orange-200',
            'silver' => 'bg-gray-100 text-gray-700 border-gray-200',
            'gold' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            default => 'bg-gray-100 text-gray-700 border-gray-200',
        };
    }

    public function getRankLabel()
    {
        return match ($this->rank) {
            'bronze' => 'برونزي',
            'silver' => 'فضي',
            'gold' => 'ذهبي',
            default => 'برونزي',
        };
    }

    public function getRankIcon()
    {
        return match ($this->rank) {
            'bronze' => '🥉',
            'silver' => '🥈',
            'gold' => '🥇',
            default => '🥉',
        };
    }
}
