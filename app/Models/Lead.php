<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected static function booted()
    {
        static::creating(function (self $lead) {
            if (empty($lead->unique_id)) {
                $lead->unique_id = self::generateUniqueId();
            }
        });
    }

    protected $fillable = [
        'user_id',
        'referral_link_id',
        'client_name',
        'company_name',
        'city',
        'client_phone',
        'email',
        'product_interest',
        'service_type',
        'expected_deal_value',
        'source',
        'contract_id',
        'status',
        'commission_amount',
        'commission_type',
        'commission_rate',
        'sector',
        'notes',
        'needs',
        'recommended_systems',
        'unique_id',
        'is_duplicate',
        'duplicate_notes',
        'approved_by',
        'approved_at',
        'subscription_renewal_date',
        'renewal_notification_sent',
    ];

    protected $casts = [
        'recommended_systems' => 'array',
        'is_duplicate' => 'boolean',
        'approved_at' => 'datetime',
        'subscription_renewal_date' => 'date',
        'renewal_notification_sent' => 'date',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_FIRST_CONTACT = 'first_contact';
    public const STATUS_CALL_IN_PROGRESS = 'call_in_progress';
    public const STATUS_APPOINTMENT = 'appointment';
    public const STATUS_QUOTATION = 'quotation';
    public const STATUS_NEGOTIATION = 'negotiation';
    public const STATUS_PAUSED = 'paused';
    public const STATUS_SOLD = 'sold';
    public const STATUS_REJECTED = 'rejected';

    public static function lifecycleStatuses(): array
    {
        return [
            self::STATUS_NEW => [
                'label' => 'جديد',
                'icon' => '🆕',
                'order' => 1,
                'badge' => 'bg-gray-100 text-gray-700 border-gray-200',
            ],
            self::STATUS_FIRST_CONTACT => [
                'label' => 'تواصل أول',
                'icon' => '📞',
                'order' => 2,
                'badge' => 'bg-blue-100 text-blue-800 border-blue-200',
            ],
            self::STATUS_CALL_IN_PROGRESS => [
                'label' => 'مكالمة جارية',
                'icon' => '🔄',
                'order' => 3,
                'badge' => 'bg-indigo-100 text-indigo-800 border-indigo-200',
            ],
            self::STATUS_APPOINTMENT => [
                'label' => 'موعد',
                'icon' => '📅',
                'order' => 4,
                'badge' => 'bg-purple-100 text-purple-800 border-purple-200',
            ],
            self::STATUS_QUOTATION => [
                'label' => 'عرض سعر',
                'icon' => '💰',
                'order' => 5,
                'badge' => 'bg-amber-100 text-amber-800 border-amber-200',
            ],
            self::STATUS_NEGOTIATION => [
                'label' => 'تفاوض',
                'icon' => '🤝',
                'order' => 6,
                'badge' => 'bg-orange-100 text-orange-800 border-orange-200',
            ],
            self::STATUS_PAUSED => [
                'label' => 'معلق',
                'icon' => '⏸️',
                'order' => 7,
                'badge' => 'bg-slate-100 text-slate-800 border-slate-200',
            ],
            self::STATUS_SOLD => [
                'label' => 'تم البيع',
                'icon' => '✅',
                'order' => 8,
                'badge' => 'bg-green-100 text-green-800 border-green-200',
            ],
            self::STATUS_REJECTED => [
                'label' => 'مرفوض',
                'icon' => '❌',
                'order' => 9,
                'badge' => 'bg-red-100 text-red-800 border-red-200',
            ],
        ];
    }

    public static function normalizeStatus(?string $status): string
    {
        $status = $status ?: self::STATUS_NEW;

        $map = [
            'under_review' => self::STATUS_NEW,
            'contacting' => self::STATUS_CALL_IN_PROGRESS,
            'contacted' => self::STATUS_FIRST_CONTACT,
            'interested' => self::STATUS_NEGOTIATION,
            'proposal_sent' => self::STATUS_QUOTATION,
            'lost' => self::STATUS_REJECTED,
            'cancelled' => self::STATUS_REJECTED,
            self::STATUS_NEW => self::STATUS_NEW,
            self::STATUS_FIRST_CONTACT => self::STATUS_FIRST_CONTACT,
            self::STATUS_CALL_IN_PROGRESS => self::STATUS_CALL_IN_PROGRESS,
            self::STATUS_APPOINTMENT => self::STATUS_APPOINTMENT,
            self::STATUS_QUOTATION => self::STATUS_QUOTATION,
            self::STATUS_NEGOTIATION => self::STATUS_NEGOTIATION,
            self::STATUS_PAUSED => self::STATUS_PAUSED,
            self::STATUS_SOLD => self::STATUS_SOLD,
            self::STATUS_REJECTED => self::STATUS_REJECTED,
        ];

        return $map[$status] ?? self::STATUS_NEW;
    }

    public static function statusLabel(?string $status): string
    {
        $normalized = self::normalizeStatus($status);
        $meta = self::lifecycleStatuses()[$normalized] ?? null;
        return $meta['label'] ?? $normalized;
    }

    public static function statusBadgeClass(?string $status): string
    {
        $normalized = self::normalizeStatus($status);
        $meta = self::lifecycleStatuses()[$normalized] ?? null;
        return $meta['badge'] ?? 'bg-gray-100 text-gray-800 border-gray-200';
    }

    public static function statusOrder(?string $status): int
    {
        $normalized = self::normalizeStatus($status);
        $meta = self::lifecycleStatuses()[$normalized] ?? null;
        return (int) ($meta['order'] ?? 999);
    }

    public function getLifecycleStatusAttribute(): string
    {
        return self::normalizeStatus($this->status);
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'lead_user')->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referralLink()
    {
        return $this->belongsTo(ReferralLink::class);
    }

    public function commission()
    {
        return $this->hasOne(Commission::class);
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'lead_service');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function subscriptionRenewals()
    {
        return $this->hasMany(SubscriptionRenewal::class);
    }

    public function pendingRenewal()
    {
        return $this->hasOne(SubscriptionRenewal::class)->where('status', 'pending');
    }

    public static function generateUniqueId()
    {
        do {
            $id = 'LD' . date('Y') . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (self::where('unique_id', $id)->exists());

        return $id;
    }

    public function checkForDuplicates()
    {
        $duplicates = self::where(function ($query) {
            $query->where('client_phone', $this->client_phone)
                ->orWhere('email', $this->email);
        })
            ->where('id', '!=', $this->id)
            ->get();

        if ($duplicates->isNotEmpty()) {
            $this->update([
                'is_duplicate' => true,
                'duplicate_notes' => 'تم العثور على عملاء مشابهين: ' . $duplicates->pluck('unique_id')->join(', ')
            ]);

            return $duplicates;
        }

        return collect();
    }

    public function approve($approverId)
    {
        $this->update([
            'approved_by' => $approverId,
            'approved_at' => now(),
            'is_duplicate' => false,
            'duplicate_notes' => null,
        ]);
    }

    public function reject($approverId, $reason)
    {
        $this->update([
            'approved_by' => $approverId,
            'approved_at' => now(),
            'duplicate_notes' => $reason,
        ]);
    }

    public function renewSubscription($renewalDate)
    {
        $this->update([
            'subscription_renewal_date' => $renewalDate,
        ]);
    }

    public function isNearRenewal($days = 30)
    {
        if (!$this->subscription_renewal_date) {
            return false;
        }

        return $this->subscription_renewal_date->diffInDays(now()) <= $days;
    }

    public function createSubscriptionRenewal($renewalDate = null)
    {
        $renewalDate = $renewalDate ?: $this->subscription_renewal_date;

        if (!$renewalDate) {
            return null;
        }

        // Check if renewal already exists
        $existingRenewal = $this->subscriptionRenewals()
            ->where('status', 'pending')
            ->where('renewal_date', $renewalDate)
            ->first();

        if ($existingRenewal) {
            return $existingRenewal;
        }

        return $this->subscriptionRenewals()->create([
            'renewal_date' => $renewalDate,
            'previous_expiry_date' => $this->subscription_renewal_date,
            'renewal_amount' => $this->subscription_amount,
            'renewal_type' => 'automatic',
            'status' => 'pending',
        ]);
    }

    public function getSubscriptionStatusBadgeClass()
    {
        return match ($this->subscription_status) {
            'active' => 'bg-green-100 text-green-800 border-green-200',
            'expired' => 'bg-red-100 text-red-800 border-red-200',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getSubscriptionStatusLabel()
    {
        return match ($this->subscription_status) {
            'active' => 'نشط',
            'expired' => 'منتهي',
            'cancelled' => 'ملغي',
            default => $this->subscription_status,
        };
    }
}
