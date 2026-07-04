<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Carbon\Carbon;

class BackupCleanupCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:cleanup-old {--days=2 : Number of days to keep backups}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up database backups older than specified days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $daysOld = (int) $this->option('days');

        $this->info("Cleaning up backups older than {$daysOld} days...");

        try {
            $backupDestination = BackupDestination::create('local', config('backup.backup.name'));
            $backups = collect($backupDestination->backups());

            if ($backups->isEmpty()) {
                $this->info('No backups found.');
                return;
            }

            $cutoffDate = Carbon::now()->subDays($daysOld);
            $backupsToDelete = $backups->filter(function (Backup $backup) use ($cutoffDate) {
                return $backup->date()->lt($cutoffDate);
            });

            if ($backupsToDelete->isEmpty()) {
                $this->info('No old backups to delete.');
                return;
            }

            $deletedCount = 0;
            foreach ($backupsToDelete as $backup) {
                $path = config('backup.backup.name') . '/' . basename($backup->path());
                if (Storage::disk('local')->exists($path)) {
                    Storage::disk('local')->delete($path);
                    $this->line("Deleted: {$backup->path()}");
                    $deletedCount++;
                }
            }

            $this->info("Successfully deleted {$deletedCount} old backup(s).");

        } catch (\Exception $e) {
            $this->error('Failed to cleanup old backups: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
