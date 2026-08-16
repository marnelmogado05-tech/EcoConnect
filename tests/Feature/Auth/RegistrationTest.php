<?php

use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;

/**
 * A valid 1x1 PNG. Built from bytes rather than UploadedFile::fake()->image() so the
 * suite does not depend on the GD extension being enabled.
 */
function fakeIdCard(): UploadedFile
{
    $png = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
    );

    return UploadedFile::fake()->createWithContent('id-card.png', $png);
}

function registrationPayload(array $overrides = []): array
{
    return array_merge([
        'fname' => 'Test',
        'lname' => 'User',
        'phone' => '09171234567',
        'id_card' => fakeIdCard(),
        'email' => 'test@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
        'terms' => 'on',
        'privacy' => 'on',
    ], $overrides);
}

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('registering sends a one-time code and does not yet create the account', function () {
    Mail::fake();

    $response = $this->post('/register', registrationPayload());

    $response->assertRedirect(route('otp.verify'));

    Mail::assertSent(OtpVerificationMail::class);

    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);
});

/**
 * The code is only ever held as a hash in the session, so the test reads it from the
 * message that was actually sent — which also proves it was emailed.
 */
function sentOtp(): string
{
    $code = null;

    Mail::assertSent(OtpVerificationMail::class, function (OtpVerificationMail $mail) use (&$code) {
        $code = $mail->otp;

        return true;
    });

    return $code;
}

test('new users can register once the one-time code is confirmed', function () {
    Mail::fake();

    $this->post('/register', registrationPayload());

    $response = $this->post('/otp-verify', [
        'otp_input' => sentOtp(),
    ]);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticated();

    $user = User::firstWhere('email', 'test@example.com');

    expect($user)->not->toBeNull()
        ->and($user->fname)->toBe('Test')
        ->and($user->lname)->toBe('User');
});

test('an incorrect one-time code does not create the account', function () {
    Mail::fake();

    $this->post('/register', registrationPayload());

    $wrongCode = str_pad((string) ((((int) sentOtp()) + 1) % 1000000), 6, '0', STR_PAD_LEFT);

    $response = $this->post('/otp-verify', ['otp_input' => $wrongCode]);

    $response->assertSessionHasErrors('otp_input');

    $this->assertGuest();
    $this->assertDatabaseCount('users', 0);
});
