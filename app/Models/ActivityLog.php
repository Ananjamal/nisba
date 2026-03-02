<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'causer_id',
        'subject_type',
        'subject_id',
        'type',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    public function causer()
    {
        return $this->belongsTo(User::class, 'causer_id');
    }

    public function subject()
    {
        return $this->morphTo();
    }

    public function scopeForModel($query, $model)
    {
        return $query->where('subject_type', get_class($model))
            ->where('subject_id', $model->id);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCauser($query, $userId)
    {
        return $query->where('causer_id', $userId);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public static function log($model, $type, $description = null, $properties = [], $causerId = null)
    {
        return self::create([
            'causer_id' => $causerId ?? auth()->id(),
            'subject_type' => get_class($model),
            'subject_id' => $model->id,
            'type' => $type,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function getTypeLabel()
    {
        return match ($this->type) {
            'created' => 'إنشاء',
            'updated' => 'تحديث',
            'deleted' => 'حذف',
            'restored' => 'استعادة',
            'status_changed' => 'تغيير الحالة',
            'password_changed' => 'تغيير كلمة المرور',
            'login' => 'تسجيل دخول',
            'logout' => 'تسجيل خروج',
            'renewal_created' => 'إنشاء تجديد',
            'renewal_completed' => 'إكمال تجديد',
            'withdrawal_requested' => 'طلب سحب',
            'withdrawal_approved' => 'موافقة على السحب',
            'deletion_requested' => 'طلب حذف',
            'deletion_approved' => 'موافقة على الحذف',
            'profile_image_updated' => 'تحديث الصورة الشخصية',
            'referral_link_generated' => 'توليد رابط الإحالة',
            'duplicate_detected' => 'اكتشاف تكرار',
            'subscription_renewed' => 'تجديد الاشتراك',
            'withdrawal_delegated' => 'تفويض سحب',
            default => $this->type,
        };
    }

    public function getTypeBadgeClass()
    {
        return match ($this->type) {
            'created', 'renewal_created', 'referral_link_generated' => 'bg-green-100 text-green-800 border-green-200',
            'updated', 'renewal_completed', 'withdrawal_approved', 'profile_image_updated', 'subscription_renewed' => 'bg-blue-100 text-blue-800 border-blue-200',
            'deleted', 'deletion_approved' => 'bg-red-100 text-red-800 border-red-200',
            'restored' => 'bg-purple-100 text-purple-800 border-purple-200',
            'status_changed', 'password_changed' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
            'login' => 'bg-teal-100 text-teal-800 border-teal-200',
            'logout' => 'bg-gray-100 text-gray-800 border-gray-200',
            'withdrawal_requested', 'deletion_requested', 'duplicate_detected' => 'bg-orange-100 text-orange-800 border-orange-200',
            default => 'bg-gray-100 text-gray-800 border-gray-200',
        };
    }

    public function getChangesSummary()
    {
        if (!isset($this->properties['old']) || !isset($this->properties['new'])) {
            return null;
        }

        $changes = [];
        foreach ($this->properties['new'] as $key => $newValue) {
            $oldValue = $this->properties['old'][$key] ?? null;

            if ($oldValue !== $newValue) {
                $changes[] = [
                    'field' => $this->getFieldLabel($key),
                    'old' => $oldValue,
                    'new' => $newValue,
                ];
            }
        }

        return $changes;
    }

    private function getFieldLabel($field)
    {
        return match ($field) {
            'name' => 'الاسم',
            'email' => 'البريد الإلكتروني',
            'phone' => 'رقم الهاتف',
            'status' => 'الحالة',
            'role' => 'الدور',
            'rank' => 'الرتبة',
            'commission_multiplier' => 'معامل العمولة',
            'profile_image' => 'صورة الملف الشخصي',
            'subscription_renewal_date' => 'تاريخ تجديد الاشتراك',
            'subscription_status' => 'حالة الاشتراك',
            'renewal_amount' => 'مبلغ التجديد',
            'final_amount' => 'المبلغ النهائي',
            'tax_amount' => 'مبلغ الضريبة',
            'client_name' => 'اسم العميل',
            'company_name' => 'اسم الشركة',
            'expected_deal_value' => 'القيمة المتوقعة للصفقة',
            'city' => 'المدينة',
            'area' => 'المنطقة',
            'delegated_to' => 'مفوض إلى',
            'notes' => 'الملاحظات',
            default => $field,
        };
    }

    public function getFormattedDescription()
    {
        if ($this->description) {
            return $this->description;
        }

        $modelClass = class_basename($this->subject_type);
        $action = $this->getTypeLabel();

        return match ($this->type) {
            'created' => "تم إنشاء {$modelClass} جديد",
            'updated' => "تم تحديث {$modelClass}",
            'deleted' => "تم حذف {$modelClass}",
            'status_changed' => "تم تغيير حالة {$modelClass}",
            'profile_image_updated' => "تم تحديث صورة الملف الشخصي لـ {$modelClass}",
            'referral_link_generated' => "تم توليد رابط إحالة جديد لـ {$modelClass}",
            'duplicate_detected' => "تم اكتشاف تكرار في {$modelClass}",
            'subscription_renewed' => "تم تجديد اشتراك {$modelClass}",
            default => "{$action} {$modelClass}",
        };
    }

    public function getSubjectName()
    {
        if (!$this->subject) {
            return 'محذوف';
        }

        if (method_exists($this->subject, 'name')) {
            return $this->subject->name;
        }

        if (method_exists($this->subject, 'client_name')) {
            return $this->subject->client_name;
        }

        return class_basename($this->subject_type) . ' #' . $this->subject_id;
    }
}
