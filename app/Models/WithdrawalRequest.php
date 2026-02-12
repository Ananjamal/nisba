<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WithdrawalRequest extends Model
{
    protected $fillable = [
        'user_id',
        'lead_id',
        'amount',
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
}
