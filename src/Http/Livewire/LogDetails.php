<?php

namespace Onamfc\DevLoggerDashboard\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Onamfc\DevLoggerDashboard\Services\FileService;
use Onamfc\DevLoggerDashboard\Services\IdeService;

class LogDetails extends Component
{
    public $logId;
    public $log;
    public $showStackTrace = false;
    public $showContext = false;
    public $filePreview = null;

    protected $fileService;
    protected $ideService;

    public function boot(FileService $fileService, IdeService $ideService)
    {
        $this->fileService = $fileService;
        $this->ideService = $ideService;
    }

    public function mount($logId)
    {
        $this->logId = $logId;
        $this->loadLog();
        $this->loadFilePreview();
    }

    public function loadLog()
    {
        $this->log = DB::table('developer_logs')->where('id', $this->logId)->first();
        
        if (!$this->log) {
            abort(404, 'Log entry not found.');
        }

        // Ensure all required properties exist with default values
        $this->log->message = $this->log->message ?? '';
        $this->log->level = $this->log->level ?? 'info';
        $this->log->status = $this->log->status ?? 'open';
        $this->log->file_path = $this->log->file_path ?? null;
        $this->log->line_number = $this->log->line_number ?? null;
        $this->log->exception_class = $this->log->exception_class ?? null;
        $this->log->stack_trace = $this->log->stack_trace ?? null;
        $this->log->request_url = $this->log->request_url ?? null;
        $this->log->request_method = $this->log->request_method ?? null;
        $this->log->user_agent = $this->log->user_agent ?? null;
        $this->log->user_id = $this->log->user_id ?? null;
        $this->log->created_at = $this->log->created_at ?? now();
        $this->log->updated_at = $this->log->updated_at ?? $this->log->created_at;

        // Decode JSON fields
        if ($this->log->context) {
            $this->log->context = json_decode($this->log->context, true);
        } else {
            $this->log->context = null;
        }
    }

    public function loadFilePreview()
    {
        if ($this->log && $this->log->file_path) {
            $this->filePreview = $this->fileService->getFilePreview(
                $this->log->file_path,
                $this->log->line_number ?? 1,
                config('devlogger-dashboard.file_path.preview_lines', 10)
            );
        }
    }

    public function toggleStackTrace()
    {
        $this->showStackTrace = !$this->showStackTrace;
    }

    public function toggleContext()
    {
        $this->showContext = !$this->showContext;
    }

    public function openInIde()
    {
        if (!$this->log || !$this->log->file_path) {
            $this->addError('ide', 'File path not available.');
            return;
        }

        try {
            $ideUrl = $this->ideService->generateIdeUrl(
                $this->log->file_path,
                $this->log->line_number ?? 1
            );
            
            $this->dispatch('open-ide-url', ['url' => $ideUrl]);
        } catch (\Exception $e) {
            $this->addError('ide', 'Failed to generate IDE URL: ' . $e->getMessage());
        }
    }

    public function copyFilePath()
    {
        $this->dispatch('copy-to-clipboard', ['text' => $this->log->file_path]);
    }

    public function markResolved()
    {
        DB::table('developer_logs')
            ->where('id', $this->logId)
            ->update(['status' => 'resolved', 'updated_at' => now()]);
        
        $this->loadLog();
        session()->flash('success', 'Log marked as resolved.');
    }

    public function markOpen()
    {
        DB::table('developer_logs')
            ->where('id', $this->logId)
            ->update(['status' => 'open', 'updated_at' => now()]);
        
        $this->loadLog();
        session()->flash('success', 'Log marked as open.');
    }

    public function deleteLog()
    {
        DB::table('developer_logs')->where('id', $this->logId)->delete();
        
        return redirect()->route('devlogger.dashboard')
            ->with('success', 'Log deleted successfully.');
    }

    public function render()
    {
        return view('devlogger-dashboard::livewire.log-details');
    }
}