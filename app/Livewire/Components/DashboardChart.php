<?php

namespace App\Livewire\Components;

use Livewire\Component;

class DashboardChart extends Component
{
    public $chartId;
    public $type = 'sales'; // sales, revenue, commissions
    public $period = 'month'; // day, week, month, year
    public $chartType = 'area'; // area, bar, line
    public $chartTitle;

    public function mount($chartId, $type = 'sales', $period = 'month', $title = 'Sales Trend')
    {
        $this->chartId = $chartId;
        $this->type = $type;
        $this->period = $period;
        $this->chartTitle = $title;
    }

    public function setPeriod($period)
    {
        $this->period = $period;
        $this->dispatch('refreshChart-' . $this->chartId, $this->getChartData());
    }

    public function setChartType($type)
    {
        $this->chartType = $type;
        $this->dispatch('changeChartType-' . $this->chartId, $type);
    }

    public function getChartData()
    {
        $query = \App\Models\Lead::query();

        // Filter by sold status if it's sales/revenue
        if ($this->type === 'sales' || $this->type === 'revenue') {
            $query->where('status', 'sold');
        }

        $startDate = match ($this->period) {
            'day' => now()->startOfDay(),
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };

        $data = [];
        $labels = [];

        if ($this->period === 'day') {
            for ($i = 0; $i < 24; $i++) {
                $hour = $startDate->copy()->addHours($i);
                $labels[] = $hour->format('H:00');
                $data[] = $this->queryData($query, $hour, 'hour');
            }
        } elseif ($this->period === 'week') {
            for ($i = 0; $i < 7; $i++) {
                $day = $startDate->copy()->addDays($i);
                $labels[] = $day->format('l');
                $data[] = $this->queryData($query, $day, 'day');
            }
        } elseif ($this->period === 'month') {
            $daysInMonth = $startDate->daysInMonth;
            for ($i = 0; $i < $daysInMonth; $i++) {
                $day = $startDate->copy()->addDays($i);
                $labels[] = $day->format('d/m');
                $data[] = $this->queryData($query, $day, 'day');
            }
        } elseif ($this->period === 'year') {
            for ($i = 0; $i < 12; $i++) {
                $month = $startDate->copy()->addMonths($i);
                $labels[] = $month->format('M');
                $data[] = $this->queryData($query, $month, 'month');
            }
        }

        return [
            'series' => [[
                'name' => $this->chartTitle,
                'data' => $data
            ]],
            'labels' => $labels
        ];
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
            // Join with commissions table
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
