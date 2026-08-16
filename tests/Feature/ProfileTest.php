<?php

use App\Models\Barangay;
use App\Models\User;

/**
 * The profile form posts discrete name parts plus a municipality and barangay; there is
 * no single `name` field. These helpers keep the required payload in one place.
 */
function profilePayload(array $overrides = []): array
{
    $barangay = Barangay::factory()->create();

    return array_merge([
        'fname' => 'Test',
        'mname' => null,
        'lname' => 'User',
        'extname' => null,
        'phone' => '09171234567',
        'email' => 'test@example.com',
        'municipality_id' => $barangay->municipality_id,
        'barangay_id' => $barangay->id,
    ], $overrides);
}

test('profile page is displayed', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->get('/profile');

    $response->assertOk();
});

test('profile information can be updated', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', profilePayload());

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $user->refresh();

    $this->assertSame('Test', $user->fname);
    $this->assertSame('User', $user->lname);
    $this->assertSame('test@example.com', $user->email);
    $this->assertNull($user->email_verified_at);
});

test('email verification status is unchanged when the email address is unchanged', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->patch('/profile', profilePayload(['email' => $user->email]));

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/profile');

    $this->assertNotNull($user->refresh()->email_verified_at);
});

test('user can delete their account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->delete('/profile', [
            'password' => 'password',
        ]);

    $response
        ->assertSessionHasNoErrors()
        ->assertRedirect('/');

    $this->assertGuest();
    $this->assertNull($user->fresh());
});

test('correct password must be provided to delete account', function () {
    $user = User::factory()->create();

    $response = $this
        ->actingAs($user)
        ->from('/profile')
        ->delete('/profile', [
            'password' => 'wrong-password',
        ]);

    $response
        ->assertSessionHasErrorsIn('userDeletion', 'password')
        ->assertRedirect('/profile');

    $this->assertNotNull($user->fresh());
});
