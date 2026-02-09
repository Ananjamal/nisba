<?php

namespace App\Exports;

use App\Models\WithdrawalRequest;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PayoutsExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    protected $filters;

    public function __construct($filters)
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = WithdrawalRequest::query()->with(['user', 'lead']);

        if (!empty($this->filters['status'])) {
            $query->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID',
            'المسوق',
            'العميل',
            'المبلغ',
            'الحالة',
            'البنك',
            'الآيبان',
            'صاحب الحساب',
            'تاريخ الطلب',
        ];
    }

    public function map($payout): array
    {
        return [
            $payout->id,
            $payout->user->name,
            $payout->client_name ?: ($payout->lead ? $payout->lead->client_name : '-'),
            $payout->amount . ' ر.س',
            $this->getStatusLabel($payout->status),
            $payout->bank_name,
            $payout->iban,
            $payout->account_holder_name,
            $payout->created_at->format('Y-m-d H:i'),
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
            'pending' => 'قيد الانتظار',
            'paid' => 'تم الدفع',
            'cancelled' => 'ملغي',
            default => $status,
        };
    }
}
