<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;

/**
 * Runs a database backup, then prunes older ones.
 *
 * The admin controller previously shelled out to `php artisan backup:run` — building the
 * command string but only executing it on Windows, so on any Linux host the backup never
 * ran. It then pruned everything older than two days regardless and reported success,
 * which meant a deploy to Linux would quietly delete the backup history and replace it
 * with nothing. Pruning now happens inside this job, only after a backup has succeeded.
 */
class CreateDatabaseBackup implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly int $keepDays = 2,
    ) {
    }

    public function handle(): void
    {
        $exitCode = Artisan::call('backup:run', ['--only-db' => true]);

        if ($exitCode !== 0) {
            throw new RuntimeException('backup:run failed: '.Artisan::output());
        }

        Log::info('Database backup created.');

        $this->pruneOlderThan($this->keepDays);
    }

    /**
     * Remove backups older than the retention window.
     */
    private function pruneOlderThan(int $days): void
    {
        try {
            $destination = BackupDestination::create('local', config('backup.backup.name'));
            $cutoff = Carbon::now()->subDays($days);

            collect($destination->backups())
                ->filter(fn (Backup $backup) => $backup->date()->lt($cutoff))
                ->each(function (Backup $backup) {
                    $path = config('backup.backup.name').'/'.basename($backup->path());

                    if (Storage::disk('local')->exists($path)) {
                        Storage::disk('local')->delete($path);
                        Log::info("Pruned old backup: {$path}");
                    }
                });
        } catch (\Throwable $e) {
            // A failed prune must not mask a backup that did succeed.
            Log::error('Failed to prune old backups: '.$e->getMessage());
        }
    }
}
