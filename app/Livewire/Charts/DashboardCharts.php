<?php

namespace App\Livewire\Charts;

use Livewire\Component;
use App\Models\User;
use App\Models\Lead;
use App\Models\WithdrawalRequest;
use Carbon\Carbon;
use Livewire\Attributes\Layout;

#[Layout('layouts.admin')]
class DashboardCharts extends Component
{
    public $startDate;
    public $endDate;
    public $comparisonStartDate;
    public $comparisonEndDate;
    public $enableComparison = false;
    public $chartType = 'revenue'; // revenue, leads, users, withdrawals

    public $useCustomDates = false;
    public $datePreset = 'this_month';

    protected $queryString = [
        'startDate',
        'endDate',
        'comparisonStartDate',
        'comparisonEndDate',
        'enableComparison',
        'chartType',
    ];

    public function mount()
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->format('Y-m-d');
        $this->comparisonStartDate = now()->subMonth()->startOfMonth()->format('Y-m-d');
        $this->comparisonEndDate = now()->subMonth()->endOfMonth()->format('Y-m-d');

        $this->datePreset = 'this_month';
    }

    public function setDatePreset($preset)
    {
        $this->datePreset = $preset;
        $this->useCustomDates = false;

        [$start, $end] = $this->resolvePresetRange($preset);
        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');

        if ($this->enableComparison) {
            $this->setComparisonToPreviousPeriod();
        }
    }

    public function enableCustomDates()
    {
        $this->useCustomDates = true;
        $this->datePreset = 'custom';
    }

    public function setComparisonToPreviousPeriod()
    {
        [$primaryStart, $primaryEnd] = $this->parseOrDefaultDates($this->startDate, $this->endDate);
        $days = $primaryStart->diffInDays($primaryEnd) + 1;

        $comparisonEnd = $primaryStart->copy()->subDay()->endOfDay();
        $comparisonStart = $comparisonEnd->copy()->subDays($days - 1)->startOfDay();

        $this->comparisonStartDate = $comparisonStart->format('Y-m-d');
        $this->comparisonEndDate = $comparisonEnd->format('Y-m-d');
    }

    public function updatedEnableComparison()
    {
        if ($this->enableComparison) {
            $this->setComparisonToPreviousPeriod();
        } else {
            $this->comparisonStartDate = null;
            $this->comparisonEndDate = null;
        }
    }

    private function resolvePresetRange(string $preset): array
    {
        $end = now()->endOfDay();

        return match ($preset) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'last_7_days' => [now()->subDays(6)->startOfDay(), $end],
            'last_30_days' => [now()->subDays(29)->startOfDay(), $end],
            'this_month' => [now()->startOfMonth(), $end],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [now()->startOfYear(), $end],
            default => [now()->startOfMonth(), $end],
        };
    }

    private function parseOrDefaultDates($start, $end): array
    {
        try {
            $s = $start ? Carbon::parse($start)->startOfDay() : null;
        } catch (\Exception $e) {
            $s = null;
        }

        try {
            $e = $end ? Carbon::parse($end)->endOfDay() : null;
        } catch (\Exception $ex) {
            $e = null;
        }

        if (!$s || !$e || $s->greaterThan($e)) {
            return [now()->startOfMonth()->startOfDay(), now()->endOfDay()];
        }

        return [$s, $e];
    }

    public function getChartData()
    {
        switch ($this->chartType) {
            case 'revenue':
                return $this->getRevenueData();
            case 'leads':
                return $this->getLeadsData();
            case 'users':
                return $this->getUsersData();
            case 'withdrawals':
                return $this->getWithdrawalsData();
            default:
                return [];
        }
    }

    private function getRevenueData()
    {
        $currentPeriod = Lead::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'sold')
            ->selectRaw('DATE(created_at) as date, SUM(expected_deal_value) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $data = [
            'labels' => array_keys($currentPeriod),
            'datasets' => [
                [
                    'label' => 'الفترة الحالية',
                    'data' => array_values($currentPeriod),
                    'borderColor' => '#3B82F6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                ]
            ]
        ];

        if ($this->enableComparison && $this->comparisonStartDate && $this->comparisonEndDate) {
            $comparisonPeriod = Lead::whereBetween('created_at', [$this->comparisonStartDate, $this->comparisonEndDate])
                ->where('status', 'sold')
                ->selectRaw('DATE(created_at) as date, SUM(expected_deal_value) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('total', 'date')
                ->toArray();

            $data['datasets'][] = [
                'label' => 'فترة المقارنة',
                'data' => array_values($comparisonPeriod),
                'borderColor' => '#10B981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
            ];
        }

        return $data;
    }

    private function getLeadsData()
    {
        $currentPeriod = Lead::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $data = [
            'labels' => array_keys($currentPeriod),
            'datasets' => [
                [
                    'label' => 'العملاء المحتملون - الفترة الحالية',
                    'data' => array_values($currentPeriod),
                    'borderColor' => '#F59E0B',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                ]
            ]
        ];

        if ($this->enableComparison && $this->comparisonStartDate && $this->comparisonEndDate) {
            $comparisonPeriod = Lead::whereBetween('created_at', [$this->comparisonStartDate, $this->comparisonEndDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('total', 'date')
                ->toArray();

            $data['datasets'][] = [
                'label' => 'العملاء المحتملون - فترة المقارنة',
                'data' => array_values($comparisonPeriod),
                'borderColor' => '#EF4444',
                'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
            ];
        }

        return $data;
    }

    private function getUsersData()
    {
        $currentPeriod = User::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $data = [
            'labels' => array_keys($currentPeriod),
            'datasets' => [
                [
                    'label' => 'المسوقون الجدد - الفترة الحالية',
                    'data' => array_values($currentPeriod),
                    'borderColor' => '#8B5CF6',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                ]
            ]
        ];

        if ($this->enableComparison && $this->comparisonStartDate && $this->comparisonEndDate) {
            $comparisonPeriod = User::whereBetween('created_at', [$this->comparisonStartDate, $this->comparisonEndDate])
                ->selectRaw('DATE(created_at) as date, COUNT(*) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('total', 'date')
                ->toArray();

            $data['datasets'][] = [
                'label' => 'المسوقون الجدد - فترة المقارنة',
                'data' => array_values($comparisonPeriod),
                'borderColor' => '#EC4899',
                'backgroundColor' => 'rgba(236, 72, 153, 0.1)',
            ];
        }

        return $data;
    }

    private function getWithdrawalsData()
    {
        $currentPeriod = WithdrawalRequest::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'approved')
            ->selectRaw('DATE(created_at) as date, SUM(final_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->pluck('total', 'date')
            ->toArray();

        $data = [
            'labels' => array_keys($currentPeriod),
            'datasets' => [
                [
                    'label' => 'السحوبات المعتمدة - الفترة الحالية',
                    'data' => array_values($currentPeriod),
                    'borderColor' => '#14B8A6',
                    'backgroundColor' => 'rgba(20, 184, 166, 0.1)',
                ]
            ]
        ];

        if ($this->enableComparison && $this->comparisonStartDate && $this->comparisonEndDate) {
            $comparisonPeriod = WithdrawalRequest::whereBetween('created_at', [$this->comparisonStartDate, $this->comparisonEndDate])
                ->where('status', 'approved')
                ->selectRaw('DATE(created_at) as date, SUM(final_amount) as total')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->pluck('total', 'date')
                ->toArray();

            $data['datasets'][] = [
                'label' => 'السحوبات المعتمدة - فترة المقارنة',
                'data' => array_values($comparisonPeriod),
                'borderColor' => '#F97316',
                'backgroundColor' => 'rgba(249, 115, 22, 0.1)',
            ];
        }

        return $data;
    }

    public function getSummaryStats()
    {
        $currentRevenue = Lead::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'sold')
            ->sum('expected_deal_value');

        $currentLeads = Lead::whereBetween('created_at', [$this->startDate, $this->endDate])->count();
        $currentUsers = User::whereBetween('created_at', [$this->startDate, $this->endDate])->count();
        $currentWithdrawals = WithdrawalRequest::whereBetween('created_at', [$this->startDate, $this->endDate])
            ->where('status', 'approved')
            ->sum('final_amount');

        $stats = [
            'revenue' => $currentRevenue,
            'leads' => $currentLeads,
            'users' => $currentUsers,
            'withdrawals' => $currentWithdrawals,
        ];

        if ($this->enableComparison && $this->comparisonStartDate && $this->comparisonEndDate) {
            $comparisonRevenue = Lead::whereBetween('created_at', [$this->comparisonStartDate, $this->comparisonEndDate])
                ->where('status', 'sold')
                ->sum('expected_deal_value');

            $comparisonLeads = Lead::whereBetween('created_at', [$this->comparisonStartDate, $this->comparisonEndDate])->count();
            $comparisonUsers = User::whereBetween('created_at', [$this->comparisonStartDate, $this->comparisonEndDate])->count();
            $comparisonWithdrawals = WithdrawalRequest::whereBetween('created_at', [$this->comparisonStartDate, $this->comparisonEndDate])
                ->where('status', 'approved')
                ->sum('final_amount');

            $stats['revenue_change'] = $this->calculateChange($currentRevenue, $comparisonRevenue);
            $stats['leads_change'] = $this->calculateChange($currentLeads, $comparisonLeads);
            $stats['users_change'] = $this->calculateChange($currentUsers, $comparisonUsers);
            $stats['withdrawals_change'] = $this->calculateChange($currentWithdrawals, $comparisonWithdrawals);
        }

        return $stats;
    }

    private function calculateChange($current, $previous)
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    public function render()
    {
        return view('livewire.charts.dashboard-charts', [
            'chartData' => $this->getChartData(),
            'summaryStats' => $this->getSummaryStats(),
        ]);
    }
}
