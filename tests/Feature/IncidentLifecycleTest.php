<?php

use App\Enums\IncidentPriority;
use App\Enums\IncidentStatus;
use App\Events\IncidentReported;
use App\Events\IncidentStatusChanged;
use App\Jobs\ResolveIncidentLocation;
use App\Mail\IncidentAssignedMail;
use App\Mail\IncidentRejectedMail;
use App\Mail\IncidentReportedEmail;
use App\Mail\IncidentResolvedMail;
use App\Models\Incident;
use App\Models\IncidentAcknowledgement;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

/*
|--------------------------------------------------------------------------
| Incident lifecycle
|--------------------------------------------------------------------------
|
| Covers the path a report takes from filing through to resolution or rejection,
| asserting the side effects at each transition. Several of these are regression tests
| for defects that made the transition impossible: the priority column was spelled
| 'Urgend', and 'Assigned' was not a permitted status at all.
|
*/

function citizen(array $attributes = []): User
{
    return User::factory()->create($attributes + ['role' => 'user', 'status' => 'Active']);
}

function admin(): User
{
    return User::factory()->admin()->create(['status' => 'Active']);
}

function officer(): User
{
    return User::factory()->police()->create(['status' => 'Active']);
}

test('a citizen can file a report', function () {
    Event::fake([IncidentReported::class]);
    Queue::fake();
    Mail::fake();

    $user = citizen();

    $response = $this->actingAs($user)->postJson('/new-report', [
        'incident_type' => 'Illegal Logging',
        'description' => 'Felled trees near the river bank.',
        'latitude' => 18.6091,
        'longitude' => 121.0783,
    ]);

    $response->assertCreated()->assertJsonPath('success', true);

    $incident = Incident::sole();

    expect($incident->user_id)->toBe($user->id)
        ->and($incident->status)->toBe(IncidentStatus::Pending)
        ->and($incident->priority)->toBe(IncidentPriority::Normal)
        ->and($incident->reference_number)->toMatch('/^DENR-\d{4}-[A-Z0-9]{6}$/');

    Event::assertDispatched(IncidentReported::class);
    Queue::assertPushed(ResolveIncidentLocation::class);
    Mail::assertQueued(IncidentReportedEmail::class);
});

test('a suspended citizen cannot file a report', function () {
    $user = citizen(['status' => 'Suspended']);

    $response = $this->actingAs($user)->postJson('/new-report', [
        'incident_type' => 'Pollution',
        'description' => 'Discharge into the creek.',
        'latitude' => 18.6091,
        'longitude' => 121.0783,
    ]);

    $response->assertForbidden();
    expect(Incident::count())->toBe(0);
});

test('an admin can assign an incident at urgent priority', function () {
    Mail::fake();

    $incident = Incident::factory()->create();
    $responder = officer();

    $response = $this->actingAs(admin())->put("/admin/incidents/assign/{$incident->id}", [
        'assigned_to' => $responder->id,
        'priority' => 'Urgent',
    ]);

    $response->assertSessionHas('success');

    $incident->refresh();

    // Both of these values were rejected by the original column definitions.
    expect($incident->status)->toBe(IncidentStatus::Assigned)
        ->and($incident->priority)->toBe(IncidentPriority::Urgent)
        ->and($incident->assigned_to)->toBe($responder->id)
        ->and($incident->assigned_at)->not->toBeNull();

    // IncidentAssignedMail is the one mailable that implements ShouldQueue, so calling
    // send() on it enqueues rather than delivers.
    Mail::assertQueued(IncidentAssignedMail::class);
});

test('assignment resolves the officer actually requested', function () {
    Mail::fake();

    $police = officer();
    $bfp = User::factory()->bfp()->create(['status' => 'Active']);
    $incident = Incident::factory()->create();

    $this->actingAs(admin())->put("/admin/incidents/assign/{$incident->id}", [
        'assigned_to' => $police->id,
        'priority' => 'Normal',
    ]);

    // The ungrouped orWhere could match any BFP user regardless of the requested id.
    expect($incident->refresh()->assigned_to)->toBe($police->id)
        ->and($incident->assigned_to)->not->toBe($bfp->id);
});

test('an officer can take an incident into action', function () {
    Mail::fake();

    $responder = officer();
    $incident = Incident::factory()->assignedTo($responder)->create();

    $response = $this->actingAs($responder)->put("/police/incidents/taken/{$incident->id}", [
        'resolution_details' => 'Patrol dispatched and the site was documented.',
    ]);

    $response->assertSessionHas('success');

    $incident->refresh();

    expect($incident->status)->toBe(IncidentStatus::InProgress)
        ->and($incident->date_taken_into_action)->not->toBeNull();

    expect(IncidentAcknowledgement::where('incident_id', $incident->id)
        ->where('officer_id', $responder->id)
        ->exists())->toBeTrue();
});

test('an admin can resolve an incident', function () {
    Mail::fake();

    $incident = Incident::factory()->status(IncidentStatus::InProgress)->create();

    $response = $this->actingAs(admin())->put("/admin/incidents/resolve/{$incident->id}", [
        'resolution_details' => 'Site restored and the report was filed with CENRO.',
    ]);

    $response->assertSessionHas('success');

    $incident->refresh();

    expect($incident->status)->toBe(IncidentStatus::Resolved)
        ->and($incident->resolution_details)->toContain('CENRO')
        ->and($incident->resolved_date)->not->toBeNull();

    Mail::assertSent(IncidentResolvedMail::class);
});

test('an admin can reject an incident and the assignment is cleared', function () {
    Mail::fake();

    $incident = Incident::factory()->assignedTo(officer())->create();

    $response = $this->actingAs(admin())->put("/admin/incidents/reject/{$incident->id}", [
        'rejection_reason' => 'Duplicate of an earlier report.',
    ]);

    $response->assertSessionHas('success');

    $incident->refresh();

    expect($incident->status)->toBe(IncidentStatus::Rejected)
        ->and($incident->rejection_reason)->toContain('Duplicate')
        ->and($incident->assigned_to)->toBeNull();

    Mail::assertSent(IncidentRejectedMail::class);
});

test('each status change announces itself exactly once', function () {
    Event::fake([IncidentStatusChanged::class]);

    $first = Incident::factory()->create();
    $second = Incident::factory()->create();

    $first->update(['status' => IncidentStatus::InProgress]);
    $second->update(['status' => IncidentStatus::Resolved]);

    // The replaced boot() hook registered a new listener on every save, so the second
    // update also re-fired the first incident's listener. Two updates, two events.
    Event::assertDispatchedTimes(IncidentStatusChanged::class, 2);
});

test('updating a field other than status announces nothing', function () {
    Event::fake([IncidentStatusChanged::class]);

    Incident::factory()->create()->update(['priority' => IncidentPriority::High]);

    Event::assertNotDispatched(IncidentStatusChanged::class);
});
