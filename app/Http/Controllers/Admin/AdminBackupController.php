<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CreateDatabaseBackup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\Backup;
use Spatie\Backup\BackupDestination\BackupDestination;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Pagination\LengthAwarePaginator;
use Carbon\Carbon;

class AdminBackupController extends Controller
{
    public function index(Request $request)
    {
        try {
            $backupDestination = BackupDestination::create('local', config('backup.backup.name'));

            $backups = collect($backupDestination->backups())
                ->map(function (Backup $backup) {
                    return [
                        'filename' => basename($backup->path()),
                        'path' => $backup->path(),
                        'size' => $this->formatBytes($backup->sizeInBytes()),
                        'date' => $backup->date()->format('Y-m-d H:i:s'),
                        'age' => $backup->date()->diffForHumans(),
                    ];
                });

            // Paginate the backups
            $perPage = 10; // Number of backups per page
            $currentPage = $request->get('page', 1);
            $paginatedBackups = new LengthAwarePaginator(
                $backups->forPage($currentPage, $perPage),
                $backups->count(),
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'pageName' => 'page']
            );

            $storageUsed = $backupDestination->usedStorage();

            return view('admin.backup', [
                'backups' => $paginatedBackups,
                'lastBackup' => $backups->first(),
                'storageUsed' => $this->formatBytes($storageUsed),
            ]);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Backup Error: ' . $e->getMessage());
        }
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    public function create()
    {
        // Queued rather than shelled out, so it runs the same way on every platform and
        // the retention prune only happens once a backup has actually succeeded.
        CreateDatabaseBackup::dispatch();

        return back()->with(
            'success',
            'Backup queued. It will appear in the list once the worker finishes writing it.'
        );
    }

    public function download(string $filename): StreamedResponse
    {
        $path = config('backup.backup.name').'/'.$filename;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'The backup file does not exist.');
        }

        return Storage::disk('local')->download($path);
    }

    public function delete(string $filename)
    {
        $path = config('backup.backup.name').'/'.$filename;

        if (!Storage::disk('local')->exists($path)) {
            abort(404, 'The backup file does not exist.');
        }

        try {
            Storage::disk('local')->delete($path);
            return back()->with('success', 'Backup deleted successfully');

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to delete backup: ' . $e->getMessage());
        }
    }

    public function cleanup()
    {
        try {
            Artisan::call('backup:clean');

            return back()
                ->with('success', 'Old backups cleaned up successfully')
                ->with('output', Artisan::output());

        } catch (\Exception $e) {
            return back()->with('error', 'Cleanup failed: ' . $e->getMessage());
        }
    }

    protected function getStorageLimit(): ?string
    {
        $disk = Storage::disk('local');
        $totalSpace = disk_total_space($disk->path(''));
        $freeSpace = disk_free_space($disk->path(''));

        if ($totalSpace === false || $freeSpace === false) {
            return null;
        }

        return $this->formatBytes($totalSpace - $freeSpace);
    }

}
