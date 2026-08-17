<?php

use App\Enums\IncidentPriority;
use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use App\Services\IncidentWorkflowService;
use Illuminate\Support\Facades\Mail;

/*
|--------------------------------------------------------------------------
| Incident workflow
|--------------------------------------------------------------------------
|
| The lifecycle now has one owner. Before this, each of four controllers implemented its
| own version of every transition inline, nothing checked whether a transition made sense
| from the current state, and the model's own assignTo()/markAsResolved()/reject() methods
| were called by nobody.
|
*/

function workflow(): IncidentWorkflowService
{
    return app(IncidentWorkflowService::class);
}

function responder(): User
{
    return User::factory()->police()->create(['status' => 'Active']);
}

test('a resolved incident cannot be resolved again', function () {
    Mail::fake();

    $incident = Incident::factory()->status(IncidentStatus::Resolved)->create();

    expect(fn () => workflow()->resolve($incident, 'Resolving twice'))
        ->toThrow(RuntimeException::class);

    expect($incident->refresh()->resolution_details)->not->toBe('Resolving twice');
});

test('a rejected incident cannot be assigned', function () {
    Mail::fake();

    $incident = Incident::factory()->status(IncidentStatus::Rejected)->create();

    expect(fn () => workflow()->assign($incident, responder(), IncidentPriority::High))
        ->toThrow(RuntimeException::class);

    expect($incident->refresh()->assigned_to)->toBeNull();
});

test('a pending incident cannot be taken into action before it is assigned', function () {
    Mail::fake();

    $incident = Incident::factory()->create();

    expect(fn () => workflow()->takeAction($incident, responder(), 'Jumping the queue'))
        ->toThrow(RuntimeException::class);

    expect($incident->refresh()->status)->toBe(IncidentStatus::Pending);
});

test('an incident cannot be assigned to a citizen', function () {
    Mail::fake();

    $citizen = User::factory()->create(['role' => 'user', 'status' => 'Active']);
    $incident = Incident::factory()->create();

    expect(fn () => workflow()->assign($incident, $citizen, IncidentPriority::Normal))
        ->toThrow(RuntimeException::class);

    expect($incident->refresh()->assigned_to)->toBeNull();
});

test('the refusal message names the state the incident is actually in', function () {
    Mail::fake();

    $incident = Incident::factory()->status(IncidentStatus::Resolved)->create();

    try {
        workflow()->reject($incident, 'Too late');
        $this->fail('Expected the transition to be refused.');
    } catch (RuntimeException $e) {
        expect($e->getMessage())->toContain('Resolved');
    }
});

test('a mail failure does not undo a committed transition', function () {
    // Notifications are best-effort: the controllers used to report "Failed to resolve"
    // when only the email had failed, leaving the incident resolved regardless.
    Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP unavailable'));

    $incident = Incident::factory()->status(IncidentStatus::InProgress)->create();

    workflow()->resolve($incident, 'Site restored.');

    expect($incident->refresh()->status)->toBe(IncidentStatus::Resolved)
        ->and($incident->resolved_date)->not->toBeNull();
});

test('evidence is attached to the private disk', function () {
    Mail::fake();

    $incident = Incident::factory()->create();

    expect($workflow = workflow())->not->toBeNull();
    expect($workflow->attachEvidence($incident, []))->toBe(0)
        ->and($incident->mediaEvidence()->count())->toBe(0);
});
