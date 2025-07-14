<?php

namespace Onamfc\DevLoggerDashboard\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LogDashboard extends Component
{
    use WithPagination;

    public $search = '';
    public $level = '';
    public $status = '';
    public $dateFrom = '';
    public $dateTo = '';
    public $perPage = 25;
    public $sortField = 'created_at';
    public $sortDirection = 'desc';
    public $selectedLogs = [];
    public $selectAll = false;
    public $showFilters = false;

    protected $queryString = [
        'search' => ['except' => ''],
        'level' => ['except' => ''],
        'status' => ['except' => ''],
        'dateFrom' => ['except' => ''],
        'dateTo' => ['except' => ''],
        'sortField' => ['except' => 'created_at'],
        'sortDirection' => ['except' => 'desc'],
    ];

    public function mount()
    {
        $this->perPage = config('devlogger-dashboard.dashboard.per_page', 25);
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedLevel()
    {
        $this->resetPage();
    }

    public function updatedStatus()
    {
        $this->resetPage();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedLogs = $this->logs->pluck('id')->toArray();
        } else {
            $this->selectedLogs = [];
        }
    }

    public function sortBy($field)
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->level = '';
        $this->status = '';
        $this->dateFrom = '';
        $this->dateTo = '';
        $this->resetPage();
    }

    public function bulkDelete()
    {
        if (empty($this->selectedLogs)) {
            $this->addError('bulk', 'No logs selected.');
            return;
        }

        $count = DB::table('developer_logs')->whereIn('id', $this->selectedLogs)->delete();
        $this->selectedLogs = [];
        $this->selectAll = false;
        
        session()->flash('success', "Successfully deleted {$count} log entries.");
        $this->resetPage();
    }

    public function bulkMarkResolved()
    {
        if (empty($this->selectedLogs)) {
            $this->addError('bulk', 'No logs selected.');
            return;
        }

        $count = DB::table('developer_logs')
            ->whereIn('id', $this->selectedLogs)
            ->update(['status' => 'resolved', 'updated_at' => now()]);
        
        $this->selectedLogs = [];
        $this->selectAll = false;
        
        session()->flash('success', "Successfully marked {$count} log entries as resolved.");
    }

    public function getLogsProperty()
    {
        $query = DB::table('developer_logs');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('log', 'like', '%' . $this->search . '%')
                  ->orWhere('exception_class', 'like', '%' . $this->search . '%')
                  ->orWhere('file_path', 'like', '%' . $this->search . '%')
                  ->orWhere('request_url', 'like', '%' . $this->search . '%');
            });
        }

        // Apply level filter
        if ($this->level) {
            $query->where('level', $this->level);
        }

        // Apply status filter
        if ($this->status) {
            $query->where('status', $this->status);
        }

        // Apply date filters
        if ($this->dateFrom) {
            $query->where('created_at', '>=', Carbon::parse($this->dateFrom)->startOfDay());
        }

        if ($this->dateTo) {
            $query->where('created_at', '<=', Carbon::parse($this->dateTo)->endOfDay());
        }

        // Exclude soft deleted records
        $query->whereNull('deleted_at');

        $results = $query->orderBy($this->sortField, $this->sortDirection)
                        ->paginate($this->perPage);
        
        // Ensure all required properties exist on each log object
        $results->getCollection()->transform(function ($log) {
            // Set default values for potentially missing properties
            $log->message = $log->log ?? '';
            $log->level = $log->level ?? 'info';
            $log->status = $log->status ?? 'open';
            $log->file_path = $log->file_path ?? null;
            $log->line_number = $log->line_number ?? null;
            $log->exception_class = $log->exception_class ?? null;
            $log->created_at = $log->created_at ?? now();
            $log->updated_at = $log->updated_at ?? $log->created_at;
            
            return $log;
        });
        
        return $results;
    }

    public function getStatsProperty()
    {
        $baseQuery = DB::table('developer_logs')->whereNull('deleted_at');
        
        return [
            'total' => $baseQuery->count(),
            'today' => $baseQuery->whereDate('created_at', today())->count(),
            'errors' => $baseQuery->where('level', 'error')->count(),
            'warnings' => $baseQuery->where('level', 'warning')->count(),
            'open' => $baseQuery->where('status', 'open')->count(),
            'resolved' => $baseQuery->where('status', 'resolved')->count(),
        ];
    }

    public function render()
    {
        return view('devlogger-dashboard::livewire.log-dashboard', [
            'logs' => $this->logs,
            'stats' => $this->stats,
        ]);
    }
}