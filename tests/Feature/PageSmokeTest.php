<?php

use App\Enums\IncidentStatus;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Page smoke tests
|--------------------------------------------------------------------------
|
| Every page, for every role, with realistic data present. These exist because a broken
| template is invisible until someone opens the page: the BFP incidents view was a
| byte-for-byte copy of the BFP dashboard and referenced variables its controller never
| passed, so it threw for every BFP officer and nothing caught it.
|
| Three pages are skipped rather than failing. They use MySQL-only date functions
| (DATE_FORMAT, MONTH, DATEDIFF) and cannot run on the SQLite test connection. Making
| those queries portable is an M4 item; the tests are written and waiting.
|
*/

function seedIncidentsFor(?User $officer = null): void
{
    Incident::factory()->count(3)->create();

    if ($officer) {
        Incident::factory()->count(2)->assignedTo($officer)->create();
        Incident::factory()->assignedTo($officer)->create(['status' => IncidentStatus::Resolved]);
    }
}

test('public pages render', function (string $uri) {
    $this->get($uri)->assertOk();
})->with(['/', '/hotspots', '/terms', '/privacy', '/track-incident', '/login', '/register']);

test('citizen pages render', function (string $uri) {
    $user = User::factory()->create(['role' => 'user', 'status' => 'Active']);
    seedIncidentsFor();
    Incident::factory()->count(2)->create(['user_id' => $user->id]);

    $this->actingAs($user)->get($uri)->assertOk();
})->with(['/dashboard', '/incidents', '/new-report', '/profile']);

test('admin pages render', function (string $uri) {
    $admin = User::factory()->admin()->create(['status' => 'Active']);
    seedIncidentsFor(User::factory()->police()->create(['status' => 'Active']));

    $this->actingAs($admin)->get($uri)->assertOk();
})->with([
    '/admin/dashboard',
    '/admin/incidents',
    '/admin/citizens',
    '/admin/manage-police',
    '/admin/manage-bfp',
]);

test('police pages render', function (string $uri) {
    $officer = User::factory()->police()->create(['status' => 'Active']);
    seedIncidentsFor($officer);

    $this->actingAs($officer)->get($uri)->assertOk();
})->with(['/police/incidents']);

test('bfp pages render', function (string $uri) {
    $officer = User::factory()->bfp()->create(['status' => 'Active']);
    seedIncidentsFor($officer);

    $this->actingAs($officer)->get($uri)->assertOk();
})->with(['/bfp/incidents']);

test('analytics and officer dashboards render', function (string $uri, string $role) {
    $user = User::factory()->role($role)->create(['status' => 'Active']);
    seedIncidentsFor($user);

    $this->actingAs($user)->get($uri)->assertOk();
})->with([
    ['/admin/analytics', 'admin'],
    ['/police/dashboard', 'police'],
    ['/bfp/dashboard', 'bfp'],
])->skip(
    fn () => DB::connection()->getDriverName() === 'sqlite',
    'Uses MySQL-only date functions (DATE_FORMAT, MONTH, DATEDIFF); portability is an M4 item.'
);
