<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup
                            {--disk= : Storage disk to use (s3, local, default: config filesystems.backup.disk)}
                            {--retention= : Number of days to retain backups (0 = keep all, default: config filesystems.backup.retention_days)}
                            {--compress : Compress the backup with gzip}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the database and upload to S3 storage';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $diskName = $this->option('disk') ?? config('filesystems.backup.disk', 's3');
        $retentionDays = (int) ($this->option('retention') ?? config('filesystems.backup.retention_days', 7));
        $compress = $this->option('compress');

        // Get database configuration
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        if (!in_array($config['driver'], ['pgsql', 'mysql', 'mariadb'])) {
            $this->error("Unsupported database driver: {$config['driver']}");
            return self::FAILURE;
        }

        // Generate backup filename
        $timestamp = now()->format('Y-m-d_His');
        $appName = str_replace(' ', '_', strtolower(config('app.name', 'laravel')));
        $filename = "{$appName}_{$connection}_{$timestamp}.sql";
        
        if ($compress) {
            $filename .= '.gz';
        }

        // Create temp directory
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $localPath = $tempDir . '/' . $filename;

        $this->info("Creating database backup: {$filename}");
        $this->info("Database: {$config['database']} ({$config['driver']})");

        try {
            // Create database dump
            $this->createDump($config, $localPath, $compress);
            
            $fileSize = filesize($localPath);
            $this->info("Backup created: " . $this->formatBytes($fileSize));

            // Upload to storage
            $disk = Storage::disk($diskName);
            $remotePath = 'backups/' . $filename;

            $this->info("Uploading to {$diskName}...");
            
            $stream = fopen($localPath, 'r');
            $disk->put($remotePath, $stream);
            fclose($stream);

            if (!$disk->exists($remotePath)) {
                throw new \Exception("Failed to upload backup to {$diskName}");
            }

            $this->info("✅ Backup uploaded successfully: {$remotePath}");

            // Clean up old backups if retention is set
            if ($retentionDays > 0) {
                $this->cleanupOldBackups($disk, $retentionDays);
            }

            // Remove local temp file
            unlink($localPath);

            Log::info('Database backup completed', [
                'filename' => $filename,
                'size' => $fileSize,
                'disk' => $diskName,
                'retention_days' => $retentionDays,
            ]);

            return self::SUCCESS;

        } catch (\Exception $e) {
            // Clean up on failure
            if (file_exists($localPath)) {
                unlink($localPath);
            }

            $this->error("Backup failed: {$e->getMessage()}");
            
            Log::error('Database backup failed', [
                'error' => $e->getMessage(),
                'disk' => $diskName,
            ]);

            return self::FAILURE;
        }
    }

    /**
     * Create database dump based on driver
     */
    protected function createDump(array $config, string $outputPath, bool $compress): void
    {
        $driver = $config['driver'];

        switch ($driver) {
            case 'pgsql':
                $this->createPostgresDump($config, $outputPath, $compress);
                break;
            case 'mysql':
            case 'mariadb':
                $this->createMysqlDump($config, $outputPath, $compress);
                break;
            default:
                throw new \Exception("Unsupported database driver: {$driver}");
        }
    }

    /**
     * Check if a command exists in the system
     */
    protected function commandExists(string $command): bool
    {
        $where = PHP_OS_FAMILY === 'Windows' ? 'where' : 'which';
        $process = new Process([$where, $command]);
        $process->run();
        return $process->isSuccessful();
    }

    /**
     * Get the full path to a database binary
     */
    protected function getBinaryPath(string $binary): string
    {
        // Check for custom path in environment
        $envVar = 'DB_BACKUP_' . strtoupper($binary) . '_PATH';
        if ($customPath = env($envVar)) {
            return $customPath;
        }

        // Check if binary exists in PATH
        if ($this->commandExists($binary)) {
            return $binary;
        }

        // Try common PostgreSQL installation paths on Windows
        if (PHP_OS_FAMILY === 'Windows') {
            $programFiles = getenv('ProgramFiles');
            $programFilesX86 = getenv('ProgramFiles(x86)');
            
            $commonPaths = [
                $programFiles . '\\PostgreSQL\\*\\bin\\' . $binary . '.exe',
                $programFilesX86 . '\\PostgreSQL\\*\\bin\\' . $binary . '.exe',
                'C:\\PostgreSQL\\*\\bin\\' . $binary . '.exe',
                'C:\\Program Files\\PostgreSQL\\*\\bin\\' . $binary . '.exe',
            ];

            foreach ($commonPaths as $pattern) {
                $matches = glob($pattern);
                if (!empty($matches)) {
                    return $matches[0];
                }
            }
        }

        throw new \Exception(
            "Database binary '{$binary}' not found. " .
            "Please install PostgreSQL/MySQL client tools or set {$envVar} environment variable. " .
            "On Windows, add PostgreSQL bin folder to PATH or use WSL."
        );
    }

    /**
     * Create PostgreSQL dump
     */
    protected function createPostgresDump(array $config, string $outputPath, bool $compress): void
    {
        $pgDumpPath = $this->getBinaryPath('pg_dump');
        
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '5432';
        $user = $config['username'];
        $database = $config['database'];
        $password = $config['password'] ?? '';

        // Build base command
        $baseCmd = escapeshellarg($pgDumpPath) . 
            ' -h ' . escapeshellarg($host) .
            ' -p ' . escapeshellarg($port) .
            ' -U ' . escapeshellarg($user) .
            ' -d ' . escapeshellarg($database) .
            ' -F p';  // Plain text format

        if ($compress) {
            // Use pipe to gzip
            $gzipPath = $this->commandExists('gzip') ? 'gzip' : 'C:\\Program Files\\Git\\usr\\bin\\gzip.exe';
            if (!file_exists($gzipPath) && $gzipPath !== 'gzip') {
                // Fallback to PHP compression
                $tempPath = $outputPath . '.tmp';
                $cmd = $baseCmd . ' > ' . escapeshellarg($tempPath);
                
                $process = Process::fromShellCommandline($cmd, null, ['PGPASSWORD' => $password]);
                $process->setTimeout(300);
                $process->run();

                if (!$process->isSuccessful()) {
                    throw new ProcessFailedException($process);
                }

                // Compress with PHP
                $this->gzipFile($tempPath, $outputPath);
                unlink($tempPath);
                return;
            }
            
            $cmd = $baseCmd . ' 2>nul | ' . escapeshellarg($gzipPath) . ' > ' . escapeshellarg($outputPath);
        } else {
            $cmd = $baseCmd . ' > ' . escapeshellarg($outputPath);
        }

        $process = Process::fromShellCommandline($cmd, null, ['PGPASSWORD' => $password]);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \Exception("PostgreSQL dump failed - empty output file");
        }
    }

    /**
     * Compress a file using PHP's zlib
     */
    protected function gzipFile(string $inputPath, string $outputPath): void
    {
        $input = fopen($inputPath, 'rb');
        $output = gzopen($outputPath, 'wb9');
        
        while (!feof($input)) {
            gzwrite($output, fread($input, 1024 * 512));
        }
        
        gzclose($output);
        fclose($input);
    }

    /**
     * Create MySQL/MariaDB dump
     */
    protected function createMysqlDump(array $config, string $outputPath, bool $compress): void
    {
        $mysqlDumpPath = $this->getBinaryPath('mysqldump');
        
        $host = $config['host'] ?? 'localhost';
        $port = $config['port'] ?? '3306';
        $user = $config['username'];
        $database = $config['database'];
        $password = $config['password'] ?? '';

        // Build base command
        $baseCmd = escapeshellarg($mysqlDumpPath) .
            ' -h ' . escapeshellarg($host) .
            ' -P ' . escapeshellarg($port) .
            ' -u ' . escapeshellarg($user) .
            ' --single-transaction' .
            ' --routines' .
            ' --triggers';

        // Add password if exists
        if (!empty($password)) {
            $baseCmd .= ' -p' . escapeshellarg($password);
        }

        $baseCmd .= ' ' . escapeshellarg($database);

        if ($compress) {
            // Try to find gzip
            $gzipPath = 'gzip';
            if (PHP_OS_FAMILY === 'Windows') {
                $possibleGzip = 'C:\\Program Files\\Git\\usr\\bin\\gzip.exe';
                if (file_exists($possibleGzip)) {
                    $gzipPath = $possibleGzip;
                } else {
                    // Use PHP compression fallback
                    $tempPath = $outputPath . '.tmp';
                    $cmd = $baseCmd . ' > ' . escapeshellarg($tempPath);
                    
                    $process = Process::fromShellCommandline($cmd);
                    $process->setTimeout(300);
                    $process->run();

                    if (!$process->isSuccessful()) {
                        throw new ProcessFailedException($process);
                    }

                    $this->gzipFile($tempPath, $outputPath);
                    unlink($tempPath);
                    return;
                }
            }
            
            $cmd = $baseCmd . ' | ' . escapeshellarg($gzipPath) . ' > ' . escapeshellarg($outputPath);
        } else {
            $cmd = $baseCmd . ' > ' . escapeshellarg($outputPath);
        }

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(300);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (!file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \Exception("MySQL dump failed - empty output file");
        }
    }

    /**
     * Clean up old backups beyond retention period
     */
    protected function cleanupOldBackups($disk, int $retentionDays): void
    {
        $this->info("Cleaning up backups older than {$retentionDays} days...");

        $cutoffDate = now()->subDays($retentionDays);
        $deletedCount = 0;
        
        // List all files in backups directory
        $files = $disk->files('backups');

        foreach ($files as $file) {
            // Skip non-sql files
            if (!str_ends_with($file, '.sql') && !str_ends_with($file, '.sql.gz')) {
                continue;
            }

            // Get last modified time
            $lastModified = $disk->lastModified($file);
            
            if ($lastModified < $cutoffDate->timestamp) {
                $disk->delete($file);
                $deletedCount++;
                $this->line("  Deleted: {$file}");
            }
        }

        $this->info("Cleaned up {$deletedCount} old backup(s)");

        Log::info('Backup cleanup completed', [
            'deleted_count' => $deletedCount,
            'retention_days' => $retentionDays,
        ]);
    }

    /**
     * Format bytes to human readable
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}
