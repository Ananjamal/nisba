<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubscriptionRenewal extends Model
{
    protected $fillable = [
        'lead_id',
        'renewal_date',
        'previous_expiry_date',
        'new_expiry_date',
        'renewal_amount',
        'renewal_type', // manual, automatic
        'renewed_by',
        'notes',
        'status', // pending, completed, failed, cancelled
        'payment_method',
        'invoice_url',
        'notification_sent_at',
        'grace_period_ends',
    ];

    protected $casts = [
        'renewal_date' => 'date',
        'previous_expiry_date' => 'date',
        'new_expiry_date' => 'date',
        'notification_sent_at' => 'datetime',
        'grace_period_ends' => 'date',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function renewedBy()
    {
        return $this->belongsTo(User::class, 'renewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query, $days = 30)
    {
        return $query->where('renewal_date', '<=', now()->addDays($days))
                    ->where('renewal_date', '>=', now())
                    ->where('status', 'pending');
    }

    public function scopeExpired($query)
    {
        return $query->where('renewal_date', '<', now())
                    ->where('status', 'pending');
    }

    public function markAsCompleted($newExpiryDate, $amount = null, $notes = null)
    {
        $this->update([
            'status' => 'completed',
            'new_expiry_date' => $newExpiryDate,
            'renewal_amount' => $amount ?? $this->renewal_amount,
            'notes' => $notes,
        ]);

        // Update the lead's subscription
        $this->lead->update([
            'subscription_renewal_date' => $newExpiryDate,
            'subscription_status' => 'active',
            'renewal_count' => $this->lead->renewal_count + 1,
        ]);

        return $this;
    }

    public function markAsFailed($reason)
    {
        $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);

        return $this;
    }

    public function sendNotification()
    {
        if ($this->notification_sent_at) {
            return false;
        }

        // Send notification to the assigned marketer
        $marketer = $this->lead->users->first();
        if ($marketer) {
            $marketer->notify(new \App\Notifications\SubscriptionRenewalNotification($this));
        }

        $this->update(['notification_sent_at' => now()]);
        return true;
    }

    public function isInGracePeriod()
    {
        return $this->grace_period_ends && $this->grace_period_ends->isFuture();
    }

    public function getDaysUntilRenewal()
    {
        return now()->diffInDays($this->renewal_date, false);
    }

    public function getStatusLabel()
    {
        return match ($this->status) {
            'pending' => 'في انتظار التجديد',
            'completed' => 'مكتمل',
            'failed' => 'فشل',
            'cancelled' => 'ملغي',
            default => $this->status,
        };
    }

    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'completed' => 'bg-green-100 text-green-800 border-green-200',
            'failed' => 'bg-red-100 text-red-800 border-red-200',
            'cancelled' => 'bg-gray-100 text-gray-800 border-gray-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }
}
