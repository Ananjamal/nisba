<?php

namespace App\Livewire\Traits;

trait WithDynamicTable
{
    public $search = '';
    public $status_filter = '';
    public $date_from = '';
    public $date_to = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $columns = [];

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleColumn($column)
    {
        if (isset($this->columns[$column])) {
            $this->columns[$column] = !$this->columns[$column];
            $this->saveTablePrefs();
        }
    }

    protected function saveTablePrefs()
    {
        $key = 'table_prefs_' . str_replace('\\', '_', static::class);
        session()->put($key, $this->columns);
    }

    protected function loadTablePrefs($defaults = [])
    {
        $key = 'table_prefs_' . str_replace('\\', '_', static::class);
        $this->columns = session()->get($key, $defaults);
    }

    public function resetFilters()
    {
        $this->reset(['search', 'status_filter', 'date_from', 'date_to']);
    }
}
