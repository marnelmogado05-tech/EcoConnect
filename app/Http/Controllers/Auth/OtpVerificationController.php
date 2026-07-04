<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\RegistrationMail;

class OtpVerificationController extends Controller
{
    /**
     * Show the OTP verification form.
     */
    public function showOtpForm()
    {
        return view('auth.otp_verify');
    }

    /**
     * Verify the OTP code and complete registration.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp' => ['required', 'digits:6'],
        ]);

        $pendingUser = $request->session()->get('pending_user');

        if (!$pendingUser) {
            return redirect()->route('register')->withErrors(['otp' => 'No pending registration found. Please register again.']);
        }

        if ($request->otp != $pendingUser['otp']) {
            return back()->withErrors(['otp' => 'Invalid OTP code. Please try again.']);
        }

        // Create the user after successful OTP validation
        $user = User::create([
            'lname' => $pendingUser['lname'],
            'fname' => $pendingUser['fname'],
            'mname' => $pendingUser['mname'],
            'extname' => $pendingUser['extname'],
            'phone' => $pendingUser['phone'],
            'id_card' => $pendingUser['id_card'],
            'email' => $pendingUser['email'],
            'password' => $pendingUser['password'],
        ]);

        // Remove pending user session data
        $request->session()->forget('pending_user');

        // Optionally, send registration confirmation mail
        Mail::to($user->email)->send(new RegistrationMail($user));

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration completed successfully!');
    }
}
