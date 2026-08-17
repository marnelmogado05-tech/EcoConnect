<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\IdCardStorage;
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
     * How long a verification code stays valid.
     */
    private const OTP_TTL_MINUTES = 10;

    /**
     * Guesses allowed before the code is discarded and must be resent.
     */
    private const OTP_MAX_ATTEMPTS = 5;

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

        // The upload goes straight to private storage and only its path travels in the
        // session. Previously the file was base64-encoded into the session itself, so a
        // 5 MB ID became roughly 6.7 MB of session payload — written twice, under two
        // different keys — before the account even existed.
        $userInput['id_card_path'] = IdCardStorage::store($request->file('id_card'), 'pending');

        $otp = $this->issueOtp($request, $userInput['email']);

        $request->session()->put('pending_user_data', $userInput);

        return redirect()->route('otp.verify')
            ->with('success', 'A verification code has been sent to your email address.');
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

        $userData = $request->session()->get('pending_user_data');

        if (! $userData) {
            return redirect()->route('register')
                ->withErrors(['otp_input' => 'Your registration session has expired. Please register again.']);
        }

        if (! $this->otpMatches($request, $request->otp_input)) {
            return back()->withErrors(['otp_input' => 'That code is not valid.'])->withInput();
        }

        // Move the ID card out of the pending area now that the account is real.
        $userData['id_card_path'] = IdCardStorage::promote($userData['id_card_path']);

        // Set explicitly rather than relying on a column default, so the account is
        // immediately visible to the notification listeners that filter on status.
        $userData['role'] = UserRole::Citizen;
        $userData['status'] = UserStatus::Active;

        $user = User::create($userData);

        $this->clearOtp($request);
        $request->session()->forget('pending_user_data');

        Mail::to($user->email)->send(new \App\Mail\RegistrationMail($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Registration completed successfully!');
    }
    /**
     * Resend OTP email
     */
    public function resendOtp(Request $request): RedirectResponse
    {
        $userData = $request->session()->get('pending_user_data');

        if (! $userData) {
            return redirect()->route('register')
                ->withErrors(['otp_input' => 'Your registration session has expired. Please register again.']);
        }

        $this->issueOtp($request, $userData['email']);

        return back()->with('success', 'A new verification code has been sent.');
    }

    /**
     * Generate a code, email it, and record only its hash.
     *
     * The previous implementation used rand() — not cryptographically secure — kept the
     * code in the session as plain text, gave it no expiry, and counted no attempts.
     * With no throttling on the verify route either, a six-digit code was a one-million
     * guess space open to unlimited attempts.
     */
    private function issueOtp(Request $request, string $email): string
    {
        $otp = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('otp', [
            'hash' => Hash::make($otp),
            'expires_at' => now()->addMinutes(self::OTP_TTL_MINUTES)->timestamp,
            'attempts' => 0,
        ]);

        Mail::to($email)->send(new \App\Mail\OtpVerificationMail($otp));

        return $otp;
    }

    /**
     * Check a submitted code, consuming one attempt.
     */
    private function otpMatches(Request $request, string $submitted): bool
    {
        $otp = $request->session()->get('otp');

        if (! is_array($otp) || now()->timestamp > $otp['expires_at']) {
            $this->clearOtp($request);

            return false;
        }

        if ($otp['attempts'] >= self::OTP_MAX_ATTEMPTS) {
            $this->clearOtp($request);

            return false;
        }

        $otp['attempts']++;
        $request->session()->put('otp', $otp);

        if (! Hash::check($submitted, $otp['hash'])) {
            return false;
        }

        return true;
    }

    private function clearOtp(Request $request): void
    {
        $request->session()->forget('otp');
    }
}
