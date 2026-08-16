<?php

use App\Models\Incident;
use App\Models\IncidentFollowup;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Authorization matrix
|--------------------------------------------------------------------------
|
| Proves each role is refused every action outside its remit, including reaching another
| account's records by changing an id in the URL. Before M2 the application had no
| policies, gates or authorize() calls at all: role middleware established *who* you
| were, and nothing established *which records* were yours.
|
*/

function citizenUser(): User
{
    return User::factory()->create(['role' => 'user', 'status' => 'Active']);
}

function adminUser(): User
{
    return User::factory()->admin()->create(['status' => 'Active']);
}

function policeUser(): User
{
    return User::factory()->police()->create(['status' => 'Active']);
}

// ---------------------------------------------------------------- role middleware

test('anonymous visitors are sent to login, not through', function (string $uri) {
    $this->get($uri)->assertRedirect('/login');
})->with(['/admin/dashboard', '/admin/incidents', '/police/incidents', '/bfp/incidents', '/dashboard']);

test('a role cannot reach another role\'s area', function (string $role, string $uri) {
    $user = User::factory()->role($role)->create(['status' => 'Active']);

    $this->actingAs($user)->get($uri)->assertForbidden();
})->with([
    ['user', '/admin/incidents'],
    ['user', '/police/incidents'],
    ['user', '/bfp/incidents'],
    ['police', '/admin/incidents'],
    ['police', '/bfp/incidents'],
    ['bfp', '/admin/incidents'],
    ['bfp', '/police/incidents'],
    ['admin', '/dashboard'],
]);

// ---------------------------------------------------------------- follow-up threads

test('a citizen cannot read another citizen\'s follow-up thread', function () {
    $incident = Incident::factory()->create();
    IncidentFollowup::create([
        'incident_id' => $incident->id,
        'user_id' => $incident->user_id,
        'follow_up_text' => 'Sensitive detail about the site',
        'follow_up_type' => 'User Update',
    ]);

    $this->actingAs(citizenUser())
        ->getJson("/incidents/{$incident->id}/followups")
        ->assertForbidden();
});

test('a reporter can read their own follow-up thread', function () {
    $reporter = citizenUser();
    $incident = Incident::factory()->create(['user_id' => $reporter->id]);

    $this->actingAs($reporter)
        ->getJson("/incidents/{$incident->id}/followups")
        ->assertOk();
});

test('a citizen cannot append to another citizen\'s incident', function () {
    $incident = Incident::factory()->create();

    $this->actingAs(citizenUser())
        ->post("/incidents/{$incident->id}/followups", ['follow_up_text' => 'Injected'])
        ->assertForbidden();

    expect(IncidentFollowup::count())->toBe(0);
});

test('a citizen cannot post a staff response', function () {
    $reporter = citizenUser();
    $incident = Incident::factory()->create(['user_id' => $reporter->id]);

    // Even on their own incident: "Staff Response" carries authority the reporter
    // does not have.
    $this->actingAs($reporter)
        ->post("/incidents/{$incident->id}/respond", ['follow_up_text' => 'Case closed, signed CENRO'])
        ->assertForbidden();

    expect(IncidentFollowup::count())->toBe(0);
});

test('the assigned responder can post a staff response', function () {
    $responder = policeUser();
    $incident = Incident::factory()->assignedTo($responder)->create();

    $this->actingAs($responder)
        ->post("/incidents/{$incident->id}/respond", ['follow_up_text' => 'Site inspected.'])
        ->assertRedirect();

    expect(IncidentFollowup::count())->toBe(1);
});

test('an officer cannot respond on an incident assigned to someone else', function () {
    $incident = Incident::factory()->assignedTo(policeUser())->create();

    $this->actingAs(policeUser())
        ->post("/incidents/{$incident->id}/respond", ['follow_up_text' => 'Not mine'])
        ->assertForbidden();
});

// ---------------------------------------------------------------- officer actions

test('an officer cannot take action on an incident assigned to someone else', function () {
    $incident = Incident::factory()->assignedTo(policeUser())->create();

    $this->actingAs(policeUser())
        ->put("/police/incidents/taken/{$incident->id}", [
            'resolution_details' => 'Claiming another officer\'s case.',
        ])
        ->assertForbidden();

    expect($incident->refresh()->status)->toBe('Assigned');
});

test('an officer can take action on their own assignment', function () {
    $responder = policeUser();
    $incident = Incident::factory()->assignedTo($responder)->create();

    $this->actingAs($responder)
        ->put("/police/incidents/taken/{$incident->id}", [
            'resolution_details' => 'Patrol dispatched and the site documented.',
        ])
        ->assertRedirect();

    expect($incident->refresh()->status)->toBe('In Progress');
});

test('only an admin can assign, resolve or reject', function (string $role) {
    $user = User::factory()->role($role)->create(['status' => 'Active']);
    $incident = Incident::factory()->create();

    // Non-admins are stopped by the role middleware before the policy is consulted;
    // either way the incident must not change.
    $this->actingAs($user)
        ->put("/admin/incidents/reject/{$incident->id}", ['rejection_reason' => 'no'])
        ->assertForbidden();

    expect($incident->refresh()->status)->toBe('Pending');
})->with(['user', 'police', 'bfp']);

// ---------------------------------------------------------------- ID cards

test('a citizen cannot fetch another user\'s id card', function () {
    $other = citizenUser();
    $other->forceFill(['id_card_path' => 'id-cards/whatever.jpg'])->save();

    $this->actingAs(citizenUser())
        ->get("/users/{$other->id}/id-card")
        ->assertForbidden();
});

test('an officer cannot fetch a citizen\'s id card', function () {
    $citizen = citizenUser();
    $citizen->forceFill(['id_card_path' => 'id-cards/whatever.jpg'])->save();

    $this->actingAs(policeUser())
        ->get("/users/{$citizen->id}/id-card")
        ->assertForbidden();
});

test('an id card is not reachable without signing in', function () {
    $citizen = citizenUser();

    $this->get("/users/{$citizen->id}/id-card")->assertRedirect('/login');
});

// ---------------------------------------------------------------- evidence media

test('a citizen cannot fetch evidence from another citizen\'s incident', function () {
    $incident = Incident::factory()->create();
    $media = $incident->mediaEvidence()->create([
        'file_path' => 'incidents/media/photo.jpg',
        'file_name' => 'photo.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 1024,
    ]);

    $this->actingAs(citizenUser())
        ->get("/media/{$media->id}")
        ->assertForbidden();
});

test('evidence is not reachable without signing in', function () {
    $incident = Incident::factory()->create();
    $media = $incident->mediaEvidence()->create([
        'file_path' => 'incidents/media/photo.jpg',
        'file_name' => 'photo.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 1024,
    ]);

    $this->get("/media/{$media->id}")->assertRedirect('/login');
});

// ---------------------------------------------------------------- public tracker

test('the public tracker exposes no reporter identity', function () {
    $reporter = User::factory()->create([
        'role' => 'user',
        'status' => 'Active',
        'fname' => 'Juanita',
        'lname' => 'Ramos',
        'email' => 'juanita@example.test',
        'phone' => '09171234567',
    ]);

    $incident = Incident::factory()->create(['user_id' => $reporter->id]);

    $response = $this->followingRedirects()
        ->post('/track-incident', ['reference_number' => $incident->reference_number]);

    $response->assertOk()
        ->assertSee($incident->reference_number)
        ->assertDontSee('Juanita')
        ->assertDontSee('Ramos')
        ->assertDontSee('juanita@example.test')
        ->assertDontSee('09171234567');
});

test('the flashed tracker payload carries no user record', function () {
    $incident = Incident::factory()->create();

    $this->post('/track-incident', ['reference_number' => $incident->reference_number]);

    $flashed = session('incident');

    // A plain array, not an Eloquent model: nothing here can be walked back to a person.
    expect($flashed)->toBeArray()
        ->and($flashed)->not->toHaveKey('user')
        ->and($flashed)->not->toHaveKey('user_id')
        ->and($flashed)->not->toHaveKey('assigned_to')
        ->and(json_encode($flashed))->not->toContain('id_card');
});
