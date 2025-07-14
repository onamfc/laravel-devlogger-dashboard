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
        $log = DB::table('developer_logs')->where('id', $id)->first();
        
        if (!$log) {
            abort(404, 'Log entry not found.');
        }

        return view('devlogger-dashboard::logs.show', compact('log'));
    }

    public function destroy($id)
    {
        $deleted = DB::table('developer_logs')->where('id', $id)->delete();
        
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

        switch ($request->action) {
            case 'delete':
                $count = DB::table('developer_logs')->whereIn('id', $request->ids)->delete();
                break;
            case 'mark_resolved':
                $count = DB::table('developer_logs')
                    ->whereIn('id', $request->ids)
                    ->update(['status' => 'resolved', 'updated_at' => now()]);
                break;
            case 'mark_open':
                $count = DB::table('developer_logs')
                    ->whereIn('id', $request->ids)
                    ->update(['status' => 'open', 'updated_at' => now()]);
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
        $log = DB::table('developer_logs')->where('id', $id)->first();
        
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