<?php

namespace App\Exports;

use App\Models\Lead;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LeadsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Lead::query()->with('users');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%")
                    ->orWhere('client_phone', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['sector'])) {
            $query->where('sector', $this->filters['sector']);
        }

        if (!empty($this->filters['affiliate'])) {
            $query->whereHas('users', function ($q) {
                $q->where('users.id', $this->filters['affiliate']);
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'اسم العميل',
            'الشركة',
            'المدينة',
            'رقم الهاتف',
            'البريد الإلكتروني',
            'المنطقة',
            'القطاع',
            'نوع العمولة',
            'معدل العمولة',
            'حالة العميل',
            'المسوقون',
            'تاريخ الإضافة',
        ];
    }

    public function map($lead): array
    {
        return [
            $lead->id,
            $lead->client_name,
            $lead->company_name,
            $lead->city,
            $lead->client_phone,
            $lead->email,
            $lead->region,
            $lead->sector,
            $lead->commission_type === 'fixed' ? 'ثابت' : 'نسبة',
            $lead->commission_rate . ($lead->commission_type === 'percent' ? '%' : ' ر.س'),
            $this->getStatusLabel($lead->status),
            $lead->users->pluck('name')->implode(', '),
            $lead->created_at->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function getStatusLabel($status)
    {
        return match ($status) {
            'under_review' => 'تحت المراجعة',
            'contacting' => 'جاري التواصل',
            'sold' => 'تم البيع',
            'cancelled' => 'ملغي',
            default => $status,
        };
    }
}
