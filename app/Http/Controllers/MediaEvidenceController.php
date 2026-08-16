<?php

namespace App\Http\Controllers;

use App\Models\MediaEvidence;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams incident evidence from private storage.
 *
 * These files are photographs and video of environmental crimes with GPS coordinates
 * attached. They were previously written to the public disk, which means every one of
 * them was readable by anyone who knew or guessed the URL — no session required. They
 * now live on the private disk and are only served to someone IncidentPolicy accepts.
 */
class MediaEvidenceController extends Controller
{
    public function show(MediaEvidence $media): StreamedResponse
    {
        $this->authorize('viewMedia', $media->incident);

        abort_unless(Storage::disk('local')->exists($media->file_path), 404);

        return Storage::disk('local')->response(
            $media->file_path,
            $media->file_name,
            [
                'Content-Type' => $media->mime_type ?: Storage::disk('local')->mimeType($media->file_path),
                // Video needs range requests for seeking to work in the browser.
                'Accept-Ranges' => 'bytes',
            ]
        );
    }
}
