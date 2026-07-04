<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Validation\Rule; // Add this import
use Illuminate\Support\Facades\DB; // Add this for DB facade
use Illuminate\Support\Facades\Log;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $municipalities = \App\Models\Municipality::all();
        $barangays = \App\Models\Barangay::all();

        $layout = auth()->user()->role === 'admin' || auth()->user()->role === 'police' || auth()->user()->role === 'bfp'
        ? 'layouts.app'
        : 'layouts.mobile-user';

        return view('profile.edit', [
            'user' => $request->user(),
            'municipalities' => $municipalities,
            'barangays' => $barangays,
            'layout' => $layout
        ]);
    }

    /**
     * Update the user's profile information.
     */
    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Validate the request with additional fields
        $validated = $request->validate([
            'fname' => ['required', 'string', 'max:255'],
            'mname' => ['nullable', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'extname' => ['nullable', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'municipality_id' => ['required', 'exists:municipalities,id'],
            'barangay_id' => ['required', 'exists:barangays,id'],
            'id_card' => ['nullable', 'image', 'mimes:jpeg,jpg,png', 'max:5120'], // 5MB
        ]);

        try {
            // Handle ID card upload (store as BLOB)
            if ($request->hasFile('id_card')) {
                // Get the file contents and store as BLOB
                $idCardFile = $request->file('id_card');
                $idCardData = file_get_contents($idCardFile->getRealPath());

                // Update the id_card field directly
                $user->id_card = $idCardData;
            }

            // Update other fields individually to avoid BLOB issues
            $user->fname = $validated['fname'];
            $user->mname = $validated['mname'];
            $user->lname = $validated['lname'];
            $user->extname = $validated['extname'];
            $user->phone = $validated['phone'];
            $user->email = $validated['email'];
            $user->municipality_id = $validated['municipality_id'];
            $user->barangay_id = $validated['barangay_id'];

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            return Redirect::route('profile.edit')->with('status', 'profile-updated');
        } catch (\Exception $e) {
            \Log::error('Profile update error: ' . $e->getMessage());
            return Redirect::route('profile.edit')->with('error', 'Failed to update profile: ' . $e->getMessage());
        }
    }

    /**
     * Download the user's ID card.
     */
    public function downloadIdCard(Request $request)
    {
        $user = $request->user();

        if (!$user->id_card) {
            return back()->with('error', 'No ID card found.');
        }

        try {
            // Convert BLOB to image response
            return response($user->id_card)
                ->header('Content-Type', 'image/jpeg')
                ->header('Content-Disposition', 'attachment; filename="id-card-' . $user->fname . '-' . $user->lname . '.jpg"');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to download ID card.');
        }
    }

    /**
     * View the user's ID card.
     */
    public function viewIdCard(Request $request)
    {
        $user = $request->user();

        if (!$user->id_card) {
            abort(404);
        }

        try {
            // Convert BLOB to image response
            return response($user->id_card)
                ->header('Content-Type', 'image/jpeg')
                ->header('Content-Disposition', 'inline; filename="id-card-' . $user->fname . '-' . $user->lname . '.jpg"');
        } catch (\Exception $e) {
            abort(404);
        }
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
