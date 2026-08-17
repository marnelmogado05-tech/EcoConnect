<?php

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Officer dashboards
|--------------------------------------------------------------------------
|
| The statistics on these pages were built from Incident::all() and then filtered with
| query-builder calls on the resulting Collection. That loaded the entire table into
| memory, matched everything, and reported system-wide totals to an individual officer.
|
*/

test('police statistics count only that officer\'s own assignments', function () {
    $mine = User::factory()->police()->create(['status' => 'Active']);
    $theirs = User::factory()->police()->create(['status' => 'Active']);

    Incident::factory()->count(2)->assignedTo($mine)->create();
    Incident::factory()->assignedTo($mine)->create(['status' => IncidentStatus::Resolved]);
    Incident::factory()->count(5)->assignedTo($theirs)->create();
    Incident::factory()->count(3)->create(); // unassigned

    $response = $this->actingAs($mine)->get('/police/incidents');

    $response->assertOk();

    $stats = $response->viewData('stats');

    expect($stats['total'])->toBe(3)
        ->and($stats['assigned'])->toBe(2)
        ->and($stats['resolved'])->toBe(1);
});

test('bfp statistics count only that officer\'s own assignments', function () {
    $mine = User::factory()->bfp()->create(['status' => 'Active']);
    $theirs = User::factory()->bfp()->create(['status' => 'Active']);

    Incident::factory()->count(4)->assignedTo($mine)->create();
    Incident::factory()->count(7)->assignedTo($theirs)->create();

    $response = $this->actingAs($mine)->get('/bfp/incidents');

    $response->assertOk();

    expect($response->viewData('stats')['total'])->toBe(4);
});

test('a search filter narrows the statistics instead of matching everything', function () {
    $responder = User::factory()->police()->create(['status' => 'Active']);

    $wanted = Incident::factory()->assignedTo($responder)->create([
        'description' => 'Mangrove clearing near the estuary',
    ]);
    Incident::factory()->count(3)->assignedTo($responder)->create([
        'description' => 'Something else entirely',
    ]);

    $response = $this->actingAs($responder)->get('/police/incidents?search=Mangrove');

    $response->assertOk();

    // Collection::where() with a closure filters by callback, so the old code returned
    // every row here rather than the one match.
    expect($response->viewData('stats')['total'])->toBe(1)
        ->and($response->viewData('incidents')->pluck('id')->all())->toBe([$wanted->id]);
});
