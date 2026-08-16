<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Serves ID card images from private storage.
 *
 * Every response here passes UserPolicy::viewIdCard first, so a card is only ever
 * returned to the person it belongs to or to an administrator.
 */
class IdCardController extends Controller
{
    /**
     * Display a user's ID card inline.
     */
    public function show(Request $request, ?User $user = null): StreamedResponse
    {
        return $this->stream($user ?? $request->user(), 'inline');
    }

    /**
     * Download a user's ID card.
     */
    public function download(Request $request, ?User $user = null): StreamedResponse
    {
        return $this->stream($user ?? $request->user(), 'attachment');
    }

    private function stream(User $user, string $disposition): StreamedResponse
    {
        $this->authorize('viewIdCard', $user);

        abort_if(blank($user->id_card_path), 404);
        abort_unless(Storage::disk('local')->exists($user->id_card_path), 404);

        $filename = 'id-card-'.str($user->name)->slug().'.'
            .pathinfo($user->id_card_path, PATHINFO_EXTENSION);

        return Storage::disk('local')->response($user->id_card_path, $filename, [
            'Content-Type' => Storage::disk('local')->mimeType($user->id_card_path),
            'Content-Disposition' => $disposition.'; filename="'.$filename.'"',
        ]);
    }
}
