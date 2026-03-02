<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDeletionRequest extends Model
{
    protected $fillable = [
        'lead_id',
        'requested_by',
        'status',
        'reason',
        'manager_approved_by',
        'manager_approved_at',
        'admin_approved_by',
        'admin_approved_at',
        'super_admin_approved_by',
        'super_admin_approved_at',
        'rejection_reason',
        'current_approval_level',
    ];

    protected $casts = [
        'manager_approved_at' => 'datetime',
        'admin_approved_at' => 'datetime',
        'super_admin_approved_at' => 'datetime',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function managerApprovedBy()
    {
        return $this->belongsTo(User::class, 'manager_approved_by');
    }

    public function adminApprovedBy()
    {
        return $this->belongsTo(User::class, 'admin_approved_by');
    }

    public function superAdminApprovedBy()
    {
        return $this->belongsTo(User::class, 'super_admin_approved_by');
    }

    public function canApprove($user)
    {
        return match ($this->current_approval_level) {
            'manager' => $user->hasRole('manager') || $user->hasRole('admin') || $user->hasRole('super-admin'),
            'admin' => $user->hasRole('admin') || $user->hasRole('super-admin'),
            'super_admin' => $user->hasRole('super-admin'),
            default => false,
        };
    }

    public function approve($approverId)
    {
        $user = User::find($approverId);

        if (!$this->canApprove($user)) {
            throw new \Exception('User does not have permission to approve at this level');
        }

        match ($this->current_approval_level) {
            'manager' => $this->update([
                'manager_approved_by' => $approverId,
                'manager_approved_at' => now(),
                'current_approval_level' => 'admin',
            ]),
            'admin' => $this->update([
                'admin_approved_by' => $approverId,
                'admin_approved_at' => now(),
                'current_approval_level' => 'super_admin',
            ]),
            'super_admin' => $this->update([
                'super_admin_approved_by' => $approverId,
                'super_admin_approved_at' => now(),
                'status' => 'approved',
            ]),
        };

        // If all approvals are complete, delete the lead
        if ($this->status === 'approved') {
            $this->lead->delete();
        }

        return $this->getNextApprovalLevel();
    }

    public function reject($approverId, $reason)
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);

        // Update the appropriate approval field based on current level
        match ($this->current_approval_level) {
            'manager' => $this->update(['manager_approved_by' => $approverId, 'manager_approved_at' => now()]),
            'admin' => $this->update(['admin_approved_by' => $approverId, 'admin_approved_at' => now()]),
            'super_admin' => $this->update(['super_admin_approved_by' => $approverId, 'super_admin_approved_at' => now()]),
        };
    }

    public function getNextApprovalLevel()
    {
        return match ($this->current_approval_level) {
            'manager' => 'admin',
            'admin' => 'super_admin',
            'super_admin' => null,
            default => 'manager',
        };
    }

    public function getProgressPercentage()
    {
        $total = 3;
        $completed = 0;

        if ($this->manager_approved_by) $completed++;
        if ($this->admin_approved_by) $completed++;
        if ($this->super_admin_approved_by) $completed++;

        return ($completed / $total) * 100;
    }
}
