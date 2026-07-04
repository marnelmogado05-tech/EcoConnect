<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use App\Mail\RegistrationMail;
use Illuminate\Support\Facades\Mail;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'fname' => ['required', 'string', 'max:255'],
            'mname' => ['nullable', 'string', 'max:255'],
            'lname' => ['required', 'string', 'max:255'],
            'extname' => ['nullable', 'string', 'max:10'],
            'phone' => ['required', 'string', 'max:20'],
            'id_card' => ['required', 'image', 'mimes:jpeg,jpg,png', 'max:5120'], // 5MB max
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'terms' => ['required', 'accepted'],
            'privacy' => ['required', 'accepted'],
        ]);

        $userInput = $request->only('fname', 'mname', 'lname', 'extname', 'phone', 'email', 'password');
        $userInput['password'] = Hash::make($userInput['password']);
        $userInput['id_card'] = base64_encode(file_get_contents($request->file('id_card')->getRealPath()));

        // Store user input data and OTP in session
        $otp = rand(100000, 999999);
        $request->session()->put('pending_user_data', $userInput);
        $request->session()->put('pending_user_id_card', $userInput['id_card']);
        $request->session()->put('otp', $otp);

        // Send OTP email
        Mail::to($userInput['email'])->send(new \App\Mail\OtpVerificationMail($otp));

        // Redirect to OTP verification page
        return redirect()->route('otp.verify')->with('success', 'OTP sent to your email. Please verify to complete registration.');
    }
    /**
     * Show OTP verification page
     */
    public function showOtpForm(Request $request)
    {
        $pendingUserData = $request->session()->get('pending_user_data');
        if (!$pendingUserData) {
            return redirect()->route('register')->with('error', 'No registration data found. Please register first.');
        }

        return view('auth.otp_verify', ['email' => $pendingUserData['email']]);
    }
    /**
     * Verify OTP and complete registration
     */
    public function verifyOtp(Request $request): RedirectResponse
    {
        $request->validate([
            'otp_input' => ['required', 'digits:6'],
        ]);

        $otp = $request->session()->get('otp');
        if (!$otp || $request->otp_input != $otp) {
            return back()->withErrors(['otp_input' => 'Invalid OTP entered.'])->withInput();
        }

        $userData = $request->session()->get('pending_user_data');
        if (!$userData) {
            return redirect()->route('register')->withErrors(['otp_input' => 'Session expired or no registration data found. Please register again.']);
        }

        $userData['id_card'] = base64_decode($request->session()->get('pending_user_id_card'));

        // Create the user
        $user = User::create($userData);

        // Clear session
        $request->session()->forget('otp');
        $request->session()->forget('pending_user_data');
        $request->session()->forget('pending_user_id_card');

        Mail::to($user->email)->send(new \App\Mail\RegistrationMail($user));

        Auth::login($user);

        return redirect()->route('dashboard')->with('success', 'Registration completed successfully!');
    }
    /**
     * Resend OTP email
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $userData = $request->session()->get('pending_user_data');
        if (!$userData) {
            return redirect()->route('register')->withErrors(['otp_input' => 'Session expired or no registration data found. Please register again.']);
        }

        $otp = rand(100000, 999999);
        $request->session()->put('otp', $otp);

        Mail::to($userData['email'])->send(new \App\Mail\OtpVerificationMail($otp));

        return back()->with('success', 'OTP resent to your email.');
    }
}
