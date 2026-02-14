<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarketerRanksExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = User::where('role', 'affiliate')->withCount('leads');

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['rank'])) {
            $query->where('rank', $this->filters['rank']);
        }

        if (!empty($this->filters['sector'])) {
            $query->where('sector', $this->filters['sector']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'الاسم',
            'رقم الهاتف',
            'البريد الإلكتروني',
            'الرتبة',
            'مضاعف العمولة',
            'عدد العملاء',
            'تاريخ التسجيل',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name,
            $user->phone ?: '-',
            $user->email,
            $user->getRankLabel(),
            $user->commission_multiplier,
            $user->leads_count,
            $user->created_at->format('Y-m-d'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
