<?php

namespace App\Livewire\Components;

use Livewire\Component;
use Carbon\Carbon;

class DashboardChart extends Component
{
    public $chartId;
    public $type = 'sales'; // sales, revenue, commissions
    public $period = 'month'; // day, week, month, year
    public $chartType = 'area'; // area, bar, line
    public $chartTitle;

    public $startDate;
    public $endDate;

    public $useCustomDates = false;
    public $datePreset = 'this_month';

    public $enableComparison = false;
    public $comparisonStartDate;
    public $comparisonEndDate;

    public function mount($chartId, $type = 'sales', $period = 'month', $title = 'Sales Trend')
    {
        $this->chartId = $chartId;
        $this->type = $type;
        $this->period = $period;
        $this->chartTitle = $title;

        [$start, $end] = $this->resolveDateRange();
        $this->startDate = $start->format('Y-m-d');
        $this->endDate = $end->format('Y-m-d');
        $this->datePreset = $this->defaultPresetForPeriod($this->period);

        $this->setComparisonToPreviousPeriod(false);
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

        $this->dispatch('refreshChart-' . $this->chartId, $this->getChartData());
    }

    public function setChartType($type)
    {
        $this->chartType = $type;
        $this->dispatch('changeChartType-' . $this->chartId, $type);
    }

    public function applyDateFilter()
    {
        $this->dispatch('refreshChart-' . $this->chartId, $this->getChartData());
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

        $this->dispatch('refreshChart-' . $this->chartId, $this->getChartData());
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
        $this->dispatch('refreshChart-' . $this->chartId, $this->getChartData());
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
            $this->dispatch('refreshChart-' . $this->chartId, $this->getChartData());
        }
    }

    public function getChartData()
    {
        $baseQuery = \App\Models\Lead::query();

        if ($this->type === 'sales' || $this->type === 'revenue') {
            $baseQuery->where('status', 'sold');
        }

        [$primaryStart, $primaryEnd] = $this->parseOrDefaultDates($this->startDate, $this->endDate);
        $bucket = $this->resolveBucket($primaryStart, $primaryEnd);

        [$labels, $primaryData] = $this->buildSeriesData($baseQuery, $primaryStart, $primaryEnd, $bucket);

        $series = [];
        $series[] = [
            'name' => $this->chartTitle,
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

        $colors = ['#16a34a'];
        if ($this->enableComparison) {
            $colors[] = $this->getComparisonColor($colors[0]);
        }

        return [
            'series' => $series,
            'labels' => $labels,
            'colors' => $colors,
        ];
    }

    private function getComparisonColor(string $base): string
    {
        return match ($base) {
            '#16a34a' => '#86efac',
            default => '#a7f3d0',
        };
    }

    private function resolveDateRange(): array
    {
        $end = now()->endOfDay();
        $start = match ($this->period) {
            'day' => now()->startOfDay(),
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
            'day' => 'today',
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

        if ($this->period === 'day') {
            return 'hour';
        }

        $days = $start->diffInDays($end);
        return $days <= 31 ? 'day' : 'month';
    }

    private function buildSeriesData($baseQuery, Carbon $start, Carbon $end, string $bucket, ?int $forcePoints = null): array
    {
        $labels = [];
        $data = [];

        $cursor = $start->copy();

        if ($bucket === 'hour') {
            $points = $forcePoints ?? 24;
            for ($i = 0; $i < $points; $i++) {
                $dt = $cursor->copy()->startOfDay()->addHours($i);
                $labels[] = $dt->format('H:00');
                $data[] = $this->queryData(clone $baseQuery, $dt, 'hour');
            }
        } elseif ($bucket === 'day') {
            $points = $forcePoints ?? ($start->diffInDays($end) + 1);
            for ($i = 0; $i < $points; $i++) {
                $dt = $cursor->copy()->addDays($i);
                $labels[] = $dt->format('Y-m-d');
                $data[] = $this->queryData(clone $baseQuery, $dt, 'day');
            }
        } else {
            $months = $forcePoints ?? ($start->diffInMonths($end) + 1);
            for ($i = 0; $i < $months; $i++) {
                $dt = $cursor->copy()->addMonths($i);
                $labels[] = $dt->format('Y-m');
                $data[] = $this->queryData(clone $baseQuery, $dt, 'month');
            }
        }

        return [$labels, $data];
    }

    private function queryData($query, $date, $type)
    {
        $q = clone $query;

        if ($type === 'hour') {
            $q->whereBetween('created_at', [$date, $date->copy()->endOfHour()]);
        } elseif ($type === 'day') {
            $q->whereDate('created_at', $date);
        } elseif ($type === 'month') {
            $q->whereYear('created_at', $date->year)->whereMonth('created_at', $date->month);
        }

        if ($this->type === 'sales') {
            return $q->count();
        } elseif ($this->type === 'revenue') {
            return $q->sum('expected_deal_value');
        } elseif ($this->type === 'commissions') {
            return \App\Models\Commission::whereIn('lead_id', $q->pluck('id'))
                ->where('status', 'approved')
                ->sum('amount');
        }

        return 0;
    }

    public function render()
    {
        return view('livewire.components.dashboard-chart', [
            'initialData' => $this->getChartData()
        ]);
    }
}
