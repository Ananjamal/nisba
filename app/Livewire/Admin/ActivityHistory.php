<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\ActivityLog;
use App\Models\User;

class ActivityHistory extends Component
{
    use WithPagination;

    public $search = '';
    public $typeFilter = 'all';
    public $causerFilter = 'all';
    public $subjectFilter = 'all';
    public $dateRangeFilter = '7'; // days
    public $selectedActivity = null;
    public $showDetailsModal = false;

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'refreshComponent' => '$refresh',
    ];

    public function getActivities()
    {
        $query = ActivityLog::with(['causer', 'subject'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('description', 'like', '%' . $this->search . '%')
                        ->orWhereHas('causer', function ($subQuery) {
                            $subQuery->where('name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('subject', function ($subQuery) {
                            $subQuery->where('name', 'like', '%' . $this->search . '%')
                                ->orWhere('client_name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->typeFilter !== 'all', function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->when($this->causerFilter !== 'all', function ($query) {
                $query->where('causer_id', $this->causerFilter);
            })
            ->when($this->subjectFilter !== 'all', function ($query) {
                $query->where('subject_type', $this->subjectFilter);
            })
            ->when($this->dateRangeFilter, function ($query) {
                $query->where('created_at', '>=', now()->subDays($this->dateRangeFilter));
            });

        return $query->latest()->paginate(20);
    }

    public function getStatistics()
    {
        $query = ActivityLog::query();

        if ($this->dateRangeFilter) {
            $query->where('created_at', '>=', now()->subDays($this->dateRangeFilter));
        }

        return [
            'total' => $query->count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => ActivityLog::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count(),
        ];
    }

    public function getActivityTypes()
    {
        return ActivityLog::distinct('type')->pluck('type')->map(function ($type) {
            return [
                'value' => $type,
                'label' => (new ActivityLog(['type' => $type]))->getTypeLabel(),
            ];
        });
    }

    public function getCausers()
    {
        return User::whereHas('activityLogs')->get(['id', 'name']);
    }

    public function getSubjectTypes()
    {
        return ActivityLog::distinct('subject_type')->pluck('subject_type')->map(function ($type) {
            return [
                'value' => $type,
                'label' => class_basename($type),
            ];
        });
    }

    public function showActivityDetails($activityId)
    {
        $this->selectedActivity = ActivityLog::with(['causer', 'subject'])->find($activityId);
        $this->showDetailsModal = true;
    }

    public function exportToCsv()
    {
        $activities = ActivityLog::with(['causer', 'subject'])
            ->when($this->typeFilter !== 'all', function ($query) {
                $query->where('type', $this->typeFilter);
            })
            ->when($this->causerFilter !== 'all', function ($query) {
                $query->where('causer_id', $this->causerFilter);
            })
            ->when($this->subjectFilter !== 'all', function ($query) {
                $query->where('subject_type', $this->subjectFilter);
            })
            ->when($this->dateRangeFilter, function ($query) {
                $query->where('created_at', '>=', now()->subDays($this->dateRangeFilter));
            })
            ->latest()
            ->get();

        $csvData = [];
        $csvData[] = ['التاريخ', 'النوع', 'المستخدم', 'الموضوع', 'الوصف', 'عنوان IP'];

        foreach ($activities as $activity) {
            $csvData[] = [
                $activity->created_at->format('Y-m-d H:i:s'),
                $activity->getTypeLabel(),
                $activity->causer?->name ?? 'نظام',
                $activity->getSubjectName(),
                $activity->getFormattedDescription(),
                $activity->ip_address,
            ];
        }

        $filename = 'activity-history-' . date('Y-m-d') . '.csv';
        $handle = fopen('php://output', 'w');

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);
        exit;
    }

    public function clearHistory()
    {
        $user = auth()->user();
        if (!$user || !$user->hasRole('super-admin')) {
            $this->dispatch('show-message', 'غير مصرح لك بهذا الإجراء', 'error');
            return;
        }

        $query = ActivityLog::query();

        if ($this->dateRangeFilter) {
            $query->where('created_at', '>=', now()->subDays($this->dateRangeFilter));
        }

        $deletedCount = $query->count();

        if ($deletedCount > 0) {
            ActivityLog::where('created_at', '>=', now()->subDays($this->dateRangeFilter))->delete();
            $this->dispatch('show-message', "تم حذف {$deletedCount} نشاط بنجاح");
        } else {
            $this->dispatch('show-message', 'لا توجد سجلات لحذفها', 'info');
        }

        $this->dispatch('refreshComponent');
    }

    public function render()
    {
        return view('livewire.admin.activity-history', [
            'activities' => $this->getActivities(),
            'statistics' => $this->getStatistics(),
            'activityTypes' => $this->getActivityTypes(),
            'causers' => $this->getCausers(),
            'subjectTypes' => $this->getSubjectTypes(),
        ]);
    }
}
