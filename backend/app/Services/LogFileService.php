<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class LogFileService
{
    /**
     * Validate that the file is within the logs directory
     * SECURITY: Prevents path traversal attacks
     */
    public function validateLogFile(string $filename): bool
    {
        $logPath = realpath(storage_path('logs'));
        $filePath = realpath(storage_path('logs/' . $filename));
        
        return $filePath !== false && str_starts_with($filePath, $logPath);
    }

    /**
     * Get list of log files with metadata
     */
    public function getLogFiles(): array
    {
        $files = [];
        $logPath = storage_path('logs');

        if (! File::isDirectory($logPath)) {
            return $files;
        }

        $finder = new Finder;
        $finder->files()->in($logPath)->sortByModifiedTime();

        foreach ($finder as $file) {
            $filename = $file->getFilename();
            $size = $this->formatBytes($file->getSize());
            $modified = date('M d H:i', $file->getMTime());
            $files[$filename] = "{$filename} ({$size}) - {$modified}";
        }

        return $files;
    }

    /**
     * Get file information
     */
    public function getFileInfo(string $filename): ?array
    {
        if (! $this->validateLogFile($filename)) {
            return null;
        }

        $path = storage_path('logs/' . $filename);

        if (! File::exists($path)) {
            return null;
        }

        return [
            'size' => $this->formatBytes(File::size($path)),
            'last_modified' => date('Y-m-d H:i:s', File::lastModified($path)),
        ];
    }

    /**
     * Read file content (with optional tail)
     */
    public function readFile(string $filename, int $tailLines = 0): ?string
    {
        if (! $this->validateLogFile($filename)) {
            return null;
        }

        $path = storage_path('logs/' . $filename);

        if (! File::exists($path)) {
            return null;
        }

        if ($tailLines > 0) {
            return $this->tailFile($path, $tailLines);
        }

        return File::get($path);
    }

    /**
     * Clear log file
     */
    public function clearFile(string $filename): bool
    {
        if (! $this->validateLogFile($filename)) {
            return false;
        }

        $path = storage_path('logs/' . $filename);

        if (! File::exists($path)) {
            return false;
        }

        File::put($path, '');
        return true;
    }

    /**
     * Get download URL for file
     */
    public function getDownloadUrl(string $filename): ?string
    {
        if (! $this->validateLogFile($filename)) {
            return null;
        }

        $path = storage_path('logs/' . $filename);

        if (! File::exists($path)) {
            return null;
        }

        return route('admin.logs.download', ['file' => $filename]);
    }

    /**
     * Read last N lines from file
     */
    protected function tailFile(string $path, int $lines): string
    {
        if (! File::exists($path)) {
            return '';
        }

        $file = new \SplFileObject($path, 'r');
        $file->seek(PHP_INT_MAX);
        $totalLines = $file->key();

        if ($totalLines <= $lines) {
            $file->rewind();
            return File::get($path);
        }

        $startLine = max(0, $totalLines - $lines);
        $file->seek($startLine);

        $content = '';
        while (! $file->eof()) {
            $content .= $file->fgets();
        }

        return $content;
    }

    /**
     * Format bytes to human readable string
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}