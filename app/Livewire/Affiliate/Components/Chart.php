<?php

namespace App\Livewire\Affiliate\Components;

use Livewire\Component;
use Carbon\Carbon;

class Chart extends Component
{
    public $chartId;
    public $type = 'sales'; // sales, revenue, commissions
    public $period = 'month'; // week, month, year
    public $chartType = 'area'; // line, bar, area
    public $chartTitle;
    public $colorInfo;

    public $startDate;
    public $endDate;

    public $useCustomDates = false;
    public $datePreset = 'this_month';

    public $enableComparison = false;
    public $comparisonStartDate;
    public $comparisonEndDate;

    public function mount($chartId, $type = 'sales', $period = 'month', $title = 'Chart', $color = 'blue', $chartType = 'area')
    {
        $this->chartId = $chartId;
        $this->type = $type;
        $this->period = $period;
        $this->chartTitle = $title;
        $this->chartType = $chartType;

        $this->colorInfo = match ($color) {
            'emerald' => ['#10b981', 'from-emerald-500 to-teal-400'],
            'amber' => ['#f59e0b', 'from-amber-500 to-orange-400'],
            'rose' => ['#f43f5e', 'from-rose-500 to-pink-500'],
            default => ['#3b82f6', 'from-blue-500 to-indigo-500'],
        };

        [$start, $end] = $this->resolveDateRange();
        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');

        $this->datePreset = $this->defaultPresetForPeriod($this->period);

        $comparisonStart = $start->copy()->sub($start->diffInDays($end) + 1, 'day');
        $comparisonEnd = $start->copy()->subDay();
        $this->comparisonStartDate = $comparisonStart->format('Y-m-d');
        $this->comparisonEndDate = $comparisonEnd->format('Y-m-d');
    }

    public function setPeriod($period)
    {
        $this->period = $period;

        [$start, $end] = $this->resolveDateRange();
        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');

        $this->useCustomDates = false;
        $this->datePreset = $this->defaultPresetForPeriod($this->period);

        $this->setComparisonToPreviousPeriod(false);

        $this->dispatch('refreshAffiliateChart-' . $this->chartId, $this->getChartData());
    }

    public function setChartType($type)
    {
        $this->chartType = $type;
        $this->dispatch('refreshAffiliateChart-' . $this->chartId, $this->getChartData());
    }

    public function applyDateFilter()
    {
        $this->dispatch('refreshAffiliateChart-' . $this->chartId, $this->getChartData());
    }

    public function setDatePreset($preset)
    {
        $this->datePreset = $preset;
        $this->useCustomDates = false;

        [$start, $end] = $this->resolvePresetRange($preset);
        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');

        if ($this->enableComparison) {
            $this->setComparisonToPreviousPeriod(false);
        }

        $this->dispatch('refreshAffiliateChart-' . $this->chartId, $this->getChartData());
    }

    public function enableCustomDates()
    {
        $this->useCustomDates = true;
        $this->datePreset = 'custom';
    }

    public function updatedEnableComparison()
    {
        if ($this->enableComparison) {
            $this->setComparisonToPreviousPeriod(false);
        }
        $this->dispatch('refreshAffiliateChart-' . $this->chartId, $this->getChartData());
    }

    public function setComparisonToPreviousPeriod($refresh = true)
    {
        [$primaryStart, $primaryEnd] = $this->parseOrDefaultDates($this->startDate, $this->endDate);
        $days = $primaryStart->diffInDays($primaryEnd) + 1;

        $comparisonEnd = $primaryStart->copy()->subDay()->endOfDay();
        $comparisonStart = $comparisonEnd->copy()->subDays($days - 1)->startOfDay();

        $this->comparisonStartDate = $comparisonStart->format('Y-m-d');
        $this->comparisonEndDate = $comparisonEnd->format('Y-m-d');

        if ($refresh) {
            $this->dispatch('refreshAffiliateChart-' . $this->chartId, $this->getChartData());
        }
    }

    public function getChartData()
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Correct logic: Assume Lead 'user_id' is the affiliate based on standard practices
        // Using direct query is safer than relying on relations if they are ambiguous (many-to-many vs one-to-many)
        $baseQuery = \App\Models\Lead::query()->where('user_id', $user->id);

        if ($this->type === 'sales' || $this->type === 'revenue') {
            $baseQuery->where('status', 'sold');
        }

        [$primaryStart, $primaryEnd] = $this->parseOrDefaultDates($this->startDate, $this->endDate);
        $bucket = $this->resolveBucket($primaryStart, $primaryEnd);

        [$labels, $primaryData] = $this->buildSeriesData($baseQuery, $primaryStart, $primaryEnd, $bucket);

        $series = [];
        $series[] = [
            'name' => $this->chartTitle ?: $this->getDefaultSeriesName(),
            'data' => $primaryData,
        ];

        if ($this->enableComparison) {
            [$comparisonStart, $comparisonEnd] = $this->parseOrDefaultDates($this->comparisonStartDate, $this->comparisonEndDate);
            [, $comparisonData] = $this->buildSeriesData($baseQuery, $comparisonStart, $comparisonEnd, $bucket, count($labels));

            $series[] = [
                'name' => 'مقارنة',
                'data' => $comparisonData,
            ];
        }

        $colors = [$this->colorInfo[0]];
        if ($this->enableComparison) {
            $colors[] = $this->getComparisonColor($this->colorInfo[0]);
        }

        return [
            'series' => $series,
            'period' => $this->period,
            'labels' => $labels,
            'type' => $this->chartType,
            'colors' => $colors,
        ];
    }

    private function resolveDateRange(): array
    {
        $end = now()->endOfDay();
        $start = match ($this->period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        return [$start, $end];
    }

    private function defaultPresetForPeriod(string $period): string
    {
        return match ($period) {
            'week' => 'last_7_days',
            'year' => 'this_year',
            default => 'this_month',
        };
    }

    private function resolvePresetRange(string $preset): array
    {
        $end = now()->endOfDay();

        return match ($preset) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'last_7_days' => [now()->subDays(6)->startOfDay(), $end],
            'last_30_days' => [now()->subDays(29)->startOfDay(), $end],
            'this_month' => [now()->startOfMonth(), $end],
            'last_month' => [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()],
            'this_year' => [now()->startOfYear(), $end],
            default => $this->resolveDateRange(),
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
            [$sDefault, $eDefault] = $this->resolveDateRange();
            return [$sDefault->copy()->startOfDay(), $eDefault->copy()->endOfDay()];
        }

        return [$s, $e];
    }

    private function resolveBucket(Carbon $start, Carbon $end): string
    {
        if ($this->period === 'year') {
            return 'month';
        }

        if ($this->period === 'week') {
            return 'day';
        }

        $days = $start->diffInDays($end);
        return $days <= 31 ? 'day' : 'month';
    }

    private function buildSeriesData($baseQuery, Carbon $start, Carbon $end, string $bucket, ?int $forcePoints = null): array
    {
        $labels = [];
        $data = [];

        $cursor = $start->copy();

        if ($bucket === 'day') {
            $points = $forcePoints ?? ($start->diffInDays($end) + 1);
            for ($i = 0; $i < $points; $i++) {
                $date = $cursor->copy()->addDays($i);
                $labels[] = $date->format('Y-m-d');
                $data[] = $this->queryData(clone $baseQuery, $date, 'day');
            }
        } else {
            $months = $forcePoints ?? ($start->diffInMonths($end) + 1);
            for ($i = 0; $i < $months; $i++) {
                $date = $cursor->copy()->addMonths($i);
                $labels[] = $date->format('Y-m');
                $data[] = $this->queryData(clone $baseQuery, $date, 'month');
            }
        }

        return [$labels, $data];
    }

    private function getDefaultSeriesName(): string
    {
        return match ($this->type) {
            'sales' => 'المبيعات',
            'revenue' => 'الإيرادات',
            'commissions' => 'العمولات',
            default => 'البيانات'
        };
    }

    private function getComparisonColor(string $base): string
    {
        return match ($base) {
            '#10b981' => '#6ee7b7',
            '#f59e0b' => '#fcd34d',
            '#f43f5e' => '#fda4af',
            default => '#93c5fd',
        };
    }

    private function queryData($query, $date, $type)
    {
        if ($type === 'day') {
            $query->whereDate('created_at', $date);
        } elseif ($type === 'month') {
            $query->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
        }

        if ($this->type === 'sales') {
            return $query->count();
        } elseif ($this->type === 'revenue') {
            return $query->sum('expected_deal_value');
        } elseif ($this->type === 'commissions') {
            $cQuery = \App\Models\Commission::query()
                ->where('user_id', auth()->id())
                ->where('status', 'approved');

            if ($type === 'day') {
                $cQuery->whereDate('created_at', $date);
            } elseif ($type === 'month') {
                $cQuery->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
            }

            return $cQuery->sum('amount');
        }

        return 0;
    }

    public function render()
    {
        return view('livewire.affiliate.components.chart', [
            'initialData' => $this->getChartData()
        ]);
    }
}
