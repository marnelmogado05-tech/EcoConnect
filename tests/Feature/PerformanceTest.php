<?php

use App\Jobs\ResolveIncidentLocation;
use App\Models\Incident;
use App\Models\MediaEvidence;
use App\Models\Municipality;
use App\Models\User;
use App\Services\LocationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Request-path cost
|--------------------------------------------------------------------------
|
| These pages used to reverse-geocode while rendering — one Nominatim call per incident
| or per distinct coordinate pair, with sleep(1) or usleep(200000) between calls to stay
| inside the rate limit. Location is now resolved once by a queued job, so the tests here
| assert the two properties that keep it that way: no outbound HTTP during a request, and
| a query count that does not grow with the number of incidents.
|
*/

function countQueries(callable $work): int
{
    DB::flushQueryLog();
    DB::enableQueryLog();

    $work();

    $count = count(DB::getQueryLog());
    DB::disableQueryLog();

    return $count;
}

function perfAdmin(): User
{
    return User::factory()->admin()->create(['status' => 'Active']);
}

function incidentsWithLocations(int $count): void
{
    Municipality::firstOrCreate(['name' => 'Claveria']);

    Incident::factory()->count($count)->create([
        'municipality_name' => 'Claveria',
        'address' => 'Poblacion, Claveria, Cagayan',
    ]);
}

test('the admin incident list makes no outbound requests', function () {
    Http::preventStrayRequests();

    incidentsWithLocations(15);

    // preventStrayRequests() turns any outbound HTTP into a failure.
    $this->actingAs(perfAdmin())->get('/admin/incidents')->assertOk();
});

test('the analytics page makes no outbound requests', function () {
    Http::preventStrayRequests();

    incidentsWithLocations(15);

    // preventStrayRequests() turns any outbound HTTP into a failure.
    $this->actingAs(perfAdmin())->get('/admin/analytics')->assertOk();
});

test('filtering the admin list by municipality is a plain query', function () {
    Http::preventStrayRequests();

    incidentsWithLocations(10);
    Incident::factory()->count(4)->create(['municipality_name' => 'Abulug']);

    $response = $this->actingAs(perfAdmin())->get('/admin/incidents?municipality=Claveria');

    $response->assertOk();

    expect($response->viewData('incidents')->total())->toBe(10);
});

test('the admin incident list query count does not grow with the table', function () {
    $admin = perfAdmin();

    incidentsWithLocations(5);
    $small = countQueries(fn () => $this->actingAs($admin)->get('/admin/incidents')->assertOk());

    incidentsWithLocations(40);
    $large = countQueries(fn () => $this->actingAs($admin)->get('/admin/incidents')->assertOk());

    // Pagination keeps the page size fixed, so the two should be within a query or two
    // of each other. Anything proportional to the row count is an N+1.
    expect($large)->toBeLessThanOrEqual($small + 2);
});

test('the citizen list counts every status in one grouped query', function () {
    $citizen = User::factory()->create(['role' => 'user', 'status' => 'Active']);

    Incident::factory()->count(6)->create(['user_id' => $citizen->id]);

    $queries = countQueries(fn () => $this->actingAs($citizen)->get('/incidents')->assertOk());

    // Previously: the paginated query, a count for the paginator, and then a separate
    // COUNT per status on top of a duplicated filter block.
    expect($queries)->toBeLessThan(12);
});

test('the media address accessor never reaches the network', function () {
    Http::preventStrayRequests();

    $incident = Incident::factory()->create();

    $stored = MediaEvidence::create([
        'incident_id' => $incident->id,
        'file_path' => 'incidents/media/photo.jpg',
        'file_name' => 'photo.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 1024,
        'latitude' => 18.6091,
        'longitude' => 121.0783,
        'address' => 'Poblacion, Claveria, Cagayan',
        'geocoded_at' => now(),
    ]);

    expect($stored->display_address)->toBe('Poblacion, Claveria, Cagayan');

    $pending = MediaEvidence::create([
        'incident_id' => $incident->id,
        'file_path' => 'incidents/media/other.jpg',
        'file_name' => 'other.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 1024,
        'latitude' => 18.6091,
        'longitude' => 121.0783,
    ]);

    expect($pending->display_address)->toBe('Resolving address…');
});

test('the resolve job records the municipality and address once', function () {
    Municipality::firstOrCreate(['name' => 'Claveria']);

    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => [
                'road' => 'Riverside Road',
                'municipality' => 'Claveria',
                'state' => 'Cagayan',
            ],
        ], 200),
    ]);

    $incident = Incident::factory()->create();
    $incident->mediaEvidence()->create([
        'file_path' => 'incidents/media/photo.jpg',
        'file_name' => 'photo.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 1024,
        'latitude' => 18.6091,
        'longitude' => 121.0783,
    ]);

    (new ResolveIncidentLocation($incident))->handle(app(LocationService::class));

    $incident->refresh();

    expect($incident->municipality_name)->toBe('Claveria')
        ->and($incident->municipality_id)->not->toBeNull()
        ->and($incident->address)->toContain('Riverside Road')
        ->and($incident->location_validated)->toBeTrue()
        ->and($incident->location_is_valid)->toBeTrue()
        ->and($incident->mediaEvidence()->first()->address)->toContain('Riverside Road');
});

test('a location outside the covered municipalities is recorded as invalid', function () {
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => ['municipality' => 'Tuguegarao', 'state' => 'Cagayan'],
        ], 200),
    ]);

    $incident = Incident::factory()->create();
    $incident->mediaEvidence()->create([
        'file_path' => 'incidents/media/photo.jpg',
        'file_name' => 'photo.jpg',
        'mime_type' => 'image/jpeg',
        'file_size' => 1024,
        'latitude' => 17.6131,
        'longitude' => 121.7269,
    ]);

    (new ResolveIncidentLocation($incident))->handle(app(LocationService::class));

    expect($incident->refresh()->location_is_valid)->toBeFalse()
        ->and($incident->municipality_name)->toBe('Tuguegarao');
});
