<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class BackupCreateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:create-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a daily database backup and cleanup old backups';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily backup process...');

        try {
            // Create the backup
            $this->info('Creating database backup...');
            Artisan::call('backup:run', ['--only-db' => true]);

            if (Artisan::output()) {
                $this->line('Backup output: ' . Artisan::output());
            }

            // Cleanup old backups (older than 2 days)
            $this->info('Cleaning up old backups...');
            Artisan::call('backup:cleanup-old', ['--days' => 2]);

            if (Artisan::output()) {
                $this->line('Cleanup output: ' . Artisan::output());
            }

            $this->info('Daily backup process completed successfully!');

        } catch (\Exception $e) {
            $this->error('Daily backup failed: ' . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
