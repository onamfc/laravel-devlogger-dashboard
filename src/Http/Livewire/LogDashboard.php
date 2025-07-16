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

    protected $listeners = [
        'logUpdated' => '$refresh',
        'logDeleted' => '$refresh'
    ];

    public function mount()
    {
        $this->perPage = config('devlogger-dashboard.dashboard.per_page', 25);
    }

    public function updatedSearch()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedLevel()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedStatus()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedDateFrom()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedDateTo()
    {
        $this->resetPage();
        $this->resetSelection();
    }

    public function updatedSelectAll()
    {
        if ($this->selectAll) {
            $this->selectedLogs = $this->getLogsQuery()->pluck('id')->toArray();
        } else {
            $this->selectedLogs = [];
        }
    }

    public function updatedSelectedLogs()
    {
        $totalLogs = $this->getLogsQuery()->count();
        $this->selectAll = count($this->selectedLogs) === $totalLogs && $totalLogs > 0;
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
        $this->resetSelection();
        
        session()->flash('success', 'Filters cleared successfully.');
    }

    public function toggleFilters()
    {
        $this->showFilters = !$this->showFilters;
    }

    public function bulkDelete()
    {
        if (empty($this->selectedLogs)) {
            $this->addError('bulk', 'No logs selected.');
            return;
        }

        $count = DB::table('developer_logs')
            ->whereIn('id', $this->selectedLogs)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
        
        $this->resetSelection();
        
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
            ->whereNull('deleted_at')
            ->update(['status' => 'resolved', 'updated_at' => now()]);
        
        $this->resetSelection();
        
        session()->flash('success', "Successfully marked {$count} log entries as resolved.");
    }

    public function bulkMarkOpen()
    {
        if (empty($this->selectedLogs)) {
            $this->addError('bulk', 'No logs selected.');
            return;
        }

        $count = DB::table('developer_logs')
            ->whereIn('id', $this->selectedLogs)
            ->whereNull('deleted_at')
            ->update(['status' => 'open', 'updated_at' => now()]);
        
        $this->resetSelection();
        
        session()->flash('success', "Successfully marked {$count} log entries as open.");
    }

    public function markLogResolved($logId)
    {
        DB::table('developer_logs')
            ->where('id', $logId)
            ->whereNull('deleted_at')
            ->update(['status' => 'resolved', 'updated_at' => now()]);
        
        session()->flash('success', 'Log marked as resolved.');
    }

    public function markLogOpen($logId)
    {
        DB::table('developer_logs')
            ->where('id', $logId)
            ->whereNull('deleted_at')
            ->update(['status' => 'open', 'updated_at' => now()]);
        
        session()->flash('success', 'Log marked as open.');
    }

    public function deleteLog($logId)
    {
        DB::table('developer_logs')
            ->where('id', $logId)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
        
        // Remove from selection if it was selected
        $this->selectedLogs = array_filter($this->selectedLogs, function($id) use ($logId) {
            return $id != $logId;
        });
        
        session()->flash('success', 'Log deleted successfully.');
    }

    protected function resetSelection()
    {
        $this->selectedLogs = [];
        $this->selectAll = false;
    }

    protected function getLogsQuery()
    {
        $query = DB::table('developer_logs');

        // Apply search filter
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('log', 'like', '%' . $this->search . '%')
                  ->orWhere('message', 'like', '%' . $this->search . '%')
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

        return $query;
    }

    public function getLogsProperty()
    {
        $results = $this->getLogsQuery()
                        ->orderBy($this->sortField, $this->sortDirection)
                        ->paginate($this->perPage);
        
        // Ensure all required properties exist on each log object
        $results->getCollection()->transform(function ($log) {
            // Set default values for potentially missing properties
            $log->message = $log->log ?? $log->message ?? '';
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
            'errors' => $baseQuery->whereIn('level', ['emergency', 'alert', 'critical', 'error'])->count(),
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