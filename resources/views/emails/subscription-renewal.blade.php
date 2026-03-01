@component('mail::message')
# تنبيه تجديد الاشتراك

مرحباً {{ $user->name }}،

هذا تنبيه بخصوص تجديد اشتراك العميل **{{ $lead->client_name }}**.

## تفاصيل الاشتراك
- **العميل:** {{ $lead->client_name }}
- **الشركة:** {{ $lead->company_name ?? 'غير محدد' }}
- **تاريخ التجديد:** {{ $subscriptionRenewal->renewal_date->format('Y-m-d') }}
- **المبلغ المطلوب:** {{ number_format($subscriptionRenewal->renewal_amount, 2) }} ريال
- **الأيام المتبقية:** {{ $daysUntilRenewal }} يوم

@if($daysUntilRenewal <= 7)
⚠️ **ملاحظة هامة:** الاشتراك سينتهي قريباً! يرجى اتخاذ الإجراءات اللازمة لتجديده.
@endif

## الإجراءات المطلوبة
1. التواصل مع العميل لمناقشة تجديد الاشتراك
2. تحديث بيانات الدفع إذا لزم الأمر
3. تأكيد عملية التجديد في النظام

@if($lead->users->isNotEmpty())
**المسوق المسؤول:** {{ $lead->users->first()->name }}
@endif

---

شكراً لاهتمامك بنظام إدارة العملاء.

@component('mail::button', ['url' => url('/admin/leads/' . $lead->id)])
عرض تفاصيل العميل
@endcomponent

مع أطيب التحيات،
فريق إدارة النظام
@endcomponent
