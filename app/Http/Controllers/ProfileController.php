<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Support\IdCardStorage;
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

        // Staff get the desktop chrome; citizens get the mobile layout.
        $layout = auth()->user()->isUser() ? 'layouts.mobile-user' : 'layouts.app';

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
            if ($request->hasFile('id_card')) {
                $previousPath = $user->id_card_path;

                $user->id_card_path = IdCardStorage::store($request->file('id_card'));

                // Only discard the old image once the replacement is safely written.
                IdCardStorage::delete($previousPath);
            }

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
            // The exception text is logged, not shown: it can carry query fragments and
            // file paths, and the user can do nothing with it either way.
            Log::error('Profile update error: '.$e->getMessage());

            return Redirect::route('profile.edit')
                ->with('error', 'We could not save your profile. Please try again.');
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
