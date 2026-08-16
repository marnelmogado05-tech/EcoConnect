<?php

use App\Mail\OtpVerificationMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| One-time code handling
|--------------------------------------------------------------------------
|
| The original flow generated the code with rand(), stored it in the session as plain
| text with no expiry, counted no attempts, and sat on unthrottled routes.
|
*/

test('the code is never stored in the session in readable form', function () {
    Mail::fake();

    $this->post('/register', registrationPayload());

    $stored = session('otp');

    expect($stored)->toBeArray()
        ->and($stored)->toHaveKeys(['hash', 'expires_at', 'attempts'])
        ->and($stored)->not->toHaveKey('code');

    // The emailed code must not appear anywhere in the stored payload.
    $emailed = sentOtp();
    expect(json_encode($stored))->not->toContain($emailed);
});

test('an expired code is refused', function () {
    Mail::fake();

    $this->post('/register', registrationPayload());
    $code = sentOtp();

    $this->travel(11)->minutes();

    $this->post('/otp-verify', ['otp_input' => $code])
        ->assertSessionHasErrors('otp_input');

    expect(User::count())->toBe(0);
});

test('a code is discarded after too many wrong guesses', function () {
    Mail::fake();

    $this->post('/register', registrationPayload());
    $code = sentOtp();

    $wrong = str_pad((string) ((((int) $code) + 1) % 1000000), 6, '0', STR_PAD_LEFT);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->post('/otp-verify', ['otp_input' => $wrong]);
    }

    // The correct code no longer works: the attempt budget is spent.
    $this->post('/otp-verify', ['otp_input' => $code])
        ->assertSessionHasErrors('otp_input');

    expect(User::count())->toBe(0);
});

test('the verify route is rate limited', function () {
    Mail::fake();

    $this->post('/register', registrationPayload());

    $responses = collect(range(1, 12))->map(
        fn () => $this->post('/otp-verify', ['otp_input' => '000000'])->status()
    );

    expect($responses)->toContain(429);
});

test('the id card is held outside the session during registration', function () {
    Mail::fake();

    $this->post('/register', registrationPayload());

    $pending = session('pending_user_data');

    // Only a path travels in the session; the image itself is on the private disk.
    expect($pending)->toHaveKey('id_card_path')
        ->and($pending['id_card_path'])->toStartWith('id-cards/')
        ->and(session()->has('pending_user_id_card'))->toBeFalse()
        ->and(strlen(json_encode($pending)))->toBeLessThan(2000);
});
