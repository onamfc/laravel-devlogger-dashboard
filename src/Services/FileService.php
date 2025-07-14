<?php

namespace Onamfc\DevLoggerDashboard\Services;

class FileService
{
    public function getFilePreview(string $filePath, int $lineNumber = 1, int $contextLines = 5): ?array
    {
        if (!config('devlogger-dashboard.file_path.show_preview', true)) {
            return null;
        }
        
        $basePath = config('devlogger-dashboard.file_path.base_path', base_path());
        
        // Ensure we have an absolute path
        if (!$this->isAbsolutePath($filePath)) {
            $filePath = $basePath . DIRECTORY_SEPARATOR . ltrim($filePath, DIRECTORY_SEPARATOR);
        }
        
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return null;
        }
        
        $lines = file($filePath, FILE_IGNORE_NEW_LINES);
        $totalLines = count($lines);
        
        $startLine = max(1, $lineNumber - $contextLines);
        $endLine = min($totalLines, $lineNumber + $contextLines);
        
        $preview = [];
        for ($i = $startLine; $i <= $endLine; $i++) {
            $preview[] = [
                'number' => $i,
                'content' => $lines[$i - 1] ?? '',
                'is_target' => $i === $lineNumber
            ];
        }
        
        return [
            'lines' => $preview,
            'total_lines' => $totalLines,
            'target_line' => $lineNumber,
            'file_path' => $filePath
        ];
    }
    
    protected function isAbsolutePath(string $path): bool
    {
        return $path[0] === DIRECTORY_SEPARATOR || (PHP_OS_FAMILY === 'Windows' && preg_match('/^[A-Z]:\\\\/', $path));
    }
}