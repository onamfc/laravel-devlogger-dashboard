<?php

namespace Onamfc\DevLoggerDashboard\Services;

class IdeService
{
    public function generateIdeUrl(string $filePath, int $lineNumber = 1): string
    {
        $ide = config('devlogger-dashboard.ide.default', 'vscode');
        $handlers = config('devlogger-dashboard.ide.handlers', []);
        
        if (!isset($handlers[$ide])) {
            throw new \InvalidArgumentException("IDE handler for '{$ide}' not found.");
        }
        
        $template = $handlers[$ide];
        $basePath = config('devlogger-dashboard.file_path.base_path', base_path());
        
        // Ensure we have an absolute path
        if (!$this->isAbsolutePath($filePath)) {
            $filePath = $basePath . DIRECTORY_SEPARATOR . ltrim($filePath, DIRECTORY_SEPARATOR);
        }
        
        return str_replace(
            ['{file}', '{line}'],
            [$filePath, $lineNumber],
            $template
        );
    }
    
    public function getSupportedIdes(): array
    {
        return config('devlogger-dashboard.ide.handlers', []);
    }
    
    protected function isAbsolutePath(string $path): bool
    {
        return $path[0] === DIRECTORY_SEPARATOR || (PHP_OS_FAMILY === 'Windows' && preg_match('/^[A-Z]:\\\\/', $path));
    }
}