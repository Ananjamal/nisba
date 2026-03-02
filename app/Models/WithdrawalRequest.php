<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'amount',
        'tax_amount',
        'tax_rate',
        'iban',
        'bank_name',
        'account_holder_name',
        'client_name',
        'company_name',
        'status',
        'invoice_url',
        'iban_proof_url',
        'bank_details',
        'payment_proof_url',
        'admin_notes',
        'finance_approved_by',
        'finance_approved_at',
        'admin_approved_by',
        'admin_approved_at',
        'rejection_reason',
        'payment_method',
        'delegated_to',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function financeApprover()
    {
        return $this->belongsTo(User::class, 'finance_approved_by');
    }

    public function adminApprover()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function delegatedTo()
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    public function calculateTax()
    {
        $taxRate = SystemSetting::get('tax_rate', 15);
        $taxAmount = ($this->amount * $taxRate) / 100;

        $this->tax_rate = $taxRate;
        $this->tax_amount = $taxAmount;

        return $taxAmount;
    }

    public function delegate($toUserId)
    {
        $this->update([
            'delegated_to' => $toUserId,
        ]);
    }

    public function isValidAmount()
    {
        $minAmount = SystemSetting::get('min_withdrawal_amount', 100);
        $maxAmount = SystemSetting::get('max_withdrawal_amount', 10000);

        return $this->amount >= $minAmount && $this->amount <= $maxAmount;
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    protected static function booted()
    {
        static::creating(function ($withdrawal) {
            $withdrawal->calculateTax();
        });
    }
}
