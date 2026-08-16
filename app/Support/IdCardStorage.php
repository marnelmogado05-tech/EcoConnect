<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * One place that knows where ID card images live and how they are named.
 *
 * They are written to the `local` disk (storage/app/private), never `public`, so there
 * is no URL that reaches them without passing through the authorisation check in
 * IdCardController.
 */
class IdCardStorage
{
    public const DIRECTORY = 'id-cards';

    /**
     * Store an uploaded ID card and return its path.
     */
    public static function store(UploadedFile $file, string $prefix = ''): string
    {
        $name = Str::ulid().'.'.$file->getClientOriginalExtension();

        $directory = $prefix !== ''
            ? self::DIRECTORY.'/'.trim($prefix, '/')
            : self::DIRECTORY;

        return $file->storeAs($directory, $name, 'local');
    }

    /**
     * Move a card out of the pending area once registration completes.
     */
    public static function promote(string $pendingPath): string
    {
        if (! Storage::disk('local')->exists($pendingPath)) {
            return $pendingPath;
        }

        $finalPath = self::DIRECTORY.'/'.basename($pendingPath);

        Storage::disk('local')->move($pendingPath, $finalPath);

        return $finalPath;
    }

    /**
     * Remove a card, ignoring one that has already gone.
     */
    public static function delete(?string $path): void
    {
        if (filled($path) && Storage::disk('local')->exists($path)) {
            Storage::disk('local')->delete($path);
        }
    }
}
