<?php

namespace App\Livewire\Affiliate\Components;

use Livewire\Component;
use Illuminate\Support\Facades\DB;

class Chart extends Component
{
    public $chartId;
    public $type = 'sales'; // sales, revenue, commissions
    public $period = 'month'; // week, month, year
    public $chartType = 'area'; // line, bar, area
    public $chartTitle;
    public $colorInfo;

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
    }

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->dispatch('refreshAffiliateChart-' . $this->chartId, $this->getChartData());
    }

    public function setChartType($type)
    {
        $this->chartType = $type;
        $this->dispatch('refreshAffiliateChart-' . $this->chartId, $this->getChartData());
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

        $endDate = now();
        $startDate = match ($this->period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $data = [];
        $labels = [];

        if ($this->period === 'week') {
            for ($i = 0; $i < 7; $i++) {
                $date = $startDate->copy()->addDays($i);
                $labels[] = $date->format('D');
                $data[] = $this->queryData(clone $baseQuery, $date, 'day');
            }
        } elseif ($this->period === 'month') {
            $daysInMonth = $startDate->daysInMonth;
            for ($i = 0; $i < $daysInMonth; $i++) {
                $date = $startDate->copy()->addDays($i);
                $labels[] = $date->format('d');
                $data[] = $this->queryData(clone $baseQuery, $date, 'day');
            }
        } elseif ($this->period === 'year') {
            for ($i = 0; $i < 12; $i++) {
                $date = $startDate->copy()->addMonths($i);
                $labels[] = $date->format('M');
                $data[] = $this->queryData(clone $baseQuery, $date, 'month');
            }
        }

        $seriesName = $this->chartTitle ?: match ($this->type) {
            'sales' => 'المبيعات',
            'revenue' => 'الإيرادات',
            'commissions' => 'العمولات',
            default => 'البيانات'
        };

        return [
            'series' => [[
                'name' => $seriesName,
                'data' => $data
            ]],
            'period' => $this->period,
            'labels' => $labels,
            'type' => $this->chartType
        ];
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
