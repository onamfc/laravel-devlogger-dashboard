<?php

namespace Onamfc\DevLoggerDashboard\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Onamfc\DevLoggerDashboard\Services\FileService;
use Onamfc\DevLoggerDashboard\Services\IdeService;

class LogController extends Controller
{
    protected $fileService;
    protected $ideService;

    public function __construct(FileService $fileService, IdeService $ideService)
    {
        $this->fileService = $fileService;
        $this->ideService = $ideService;
    }

    public function show($id)
    {
        $log = DB::table('developer_logs')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
        
        if (!$log) {
            abort(404, 'Log entry not found.');
        }

        // Ensure all required properties exist with default values
        $log->message = $log->log ?? '';
        $log->level = $log->level ?? 'info';
        $log->status = $log->status ?? 'open';
        $log->file_path = $log->file_path ?? null;
        $log->line_number = $log->line_number ?? null;
        $log->exception_class = $log->exception_class ?? null;
        $log->stack_trace = $log->stack_trace ?? null;
        $log->request_url = $log->request_url ?? null;
        $log->request_method = $log->request_method ?? null;
        $log->user_agent = $log->user_agent ?? null;
        $log->user_id = $log->user_id ?? null;
        $log->ip_address = $log->ip_address ?? null;
        $log->queue = $log->queue ?? null;
        $log->tags = $log->tags ?? null;
        $log->created_at = $log->created_at ?? now();
        $log->updated_at = $log->updated_at ?? $log->created_at;
        
        // Decode JSON fields
        if (isset($log->context) && $log->context) {
            $log->context = json_decode($log->context, true);
        } else {
            $log->context = null;
        }
        
        if (isset($log->tags) && $log->tags) {
            $log->tags = json_decode($log->tags, true);
        } else {
            $log->tags = null;
        }
        
        return view('devlogger-dashboard::db-logs.show', compact('log'));
    }

    public function destroy($id)
    {
        // Use soft delete instead of hard delete
        $deleted = DB::table('developer_logs')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->update(['deleted_at' => now()]);
        
        if ($deleted) {
            return response()->json(['success' => true, 'message' => 'Log deleted successfully.']);
        }
        
        return response()->json(['success' => false, 'message' => 'Log not found.'], 404);
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:delete,mark_resolved,mark_open',
            'ids' => 'required|array',
            'ids.*' => 'integer'
        ]);

        $count = 0;
        $baseQuery = DB::table('developer_logs')
            ->whereIn('id', $request->ids)
            ->whereNull('deleted_at');

        switch ($request->action) {
            case 'delete':
                // Use soft delete
                $count = $baseQuery->update(['deleted_at' => now()]);
                break;
            case 'mark_resolved':
                $count = $baseQuery->update(['status' => 'resolved', 'updated_at' => now()]);
                break;
            case 'mark_open':
                $count = $baseQuery->update(['status' => 'open', 'updated_at' => now()]);
                break;
        }

        return response()->json([
            'success' => true,
            'message' => "Successfully processed {$count} log entries.",
            'count' => $count
        ]);
    }

    public function openFile($id)
    {
        $log = DB::table('developer_logs')
            ->where('id', $id)
            ->whereNull('deleted_at')
            ->first();
        
        if (!$log || !$log->file_path) {
            return response()->json(['success' => false, 'message' => 'File path not found.']);
        }

        $ideUrl = $this->ideService->generateIdeUrl($log->file_path, $log->line_number ?? 1);
        
        return response()->json([
            'success' => true,
            'ide_url' => $ideUrl,
            'file_path' => $log->file_path,
            'line_number' => $log->line_number ?? 1
        ]);
    }
}