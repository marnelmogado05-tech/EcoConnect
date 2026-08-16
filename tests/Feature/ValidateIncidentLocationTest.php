<?php

use App\Jobs\ValidateIncidentLocation;
use App\Models\Incident;
use App\Services\LocationService;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Location validation
|--------------------------------------------------------------------------
|
| The three columns this job writes were missing from Incident::$fillable, so every
| update was silently discarded by mass-assignment protection while the job logged
| success. The video coordinates were dropped separately: the controller passed five
| constructor arguments to a three-argument job.
|
*/

function fakeNominatim(string $municipality): void
{
    Http::fake([
        'nominatim.openstreetmap.org/*' => Http::response([
            'address' => ['municipality' => $municipality],
        ], 200),
    ]);
}

function runValidation(Incident $incident, array $args = []): void
{
    (new ValidateIncidentLocation(
        $incident,
        $args['photoLat'] ?? [],
        $args['photoLng'] ?? [],
        $args['videoLat'] ?? [],
        $args['videoLng'] ?? [],
    ))->handle(app(LocationService::class));
}

test('a covered municipality is recorded as valid', function () {
    fakeNominatim('Claveria');

    $incident = Incident::factory()->create();

    runValidation($incident, ['photoLat' => [18.6091], 'photoLng' => [121.0783]]);

    $incident->refresh();

    expect($incident->location_validated)->toBeTrue()
        ->and($incident->location_is_valid)->toBeTrue()
        ->and($incident->validated_locations)->toHaveCount(1)
        ->and($incident->validated_locations[0]['municipality'])->toBe('Claveria');
});

test('a municipality outside the covered area is recorded as invalid', function () {
    fakeNominatim('Tuguegarao');

    $incident = Incident::factory()->create();

    runValidation($incident, ['photoLat' => [17.6131], 'photoLng' => [121.7269]]);

    $incident->refresh();

    expect($incident->location_validated)->toBeTrue()
        ->and($incident->location_is_valid)->toBeFalse()
        ->and($incident->validated_locations[0]['is_valid'])->toBeFalse();
});

test('video coordinates are validated too', function () {
    fakeNominatim('Pamplona');

    $incident = Incident::factory()->create();

    // A report backed only by video previously reached the job with empty arrays,
    // because the extra two constructor arguments were silently discarded.
    runValidation($incident, ['videoLat' => [18.4667], 'videoLng' => [121.3333]]);

    $incident->refresh();

    expect($incident->validated_locations)->toHaveCount(1)
        ->and($incident->location_is_valid)->toBeTrue();
});

test('an incident with no coordinates is still marked as checked', function () {
    fakeNominatim('Claveria');

    $incident = Incident::factory()->create();

    runValidation($incident);

    $incident->refresh();

    expect($incident->location_validated)->toBeTrue()
        ->and($incident->location_is_valid)->toBeFalse()
        ->and($incident->validated_locations)->toBe([]);
});

test('non-numeric coordinates are skipped rather than geocoded', function () {
    Http::fake();

    $incident = Incident::factory()->create();

    runValidation($incident, ['photoLat' => ['', null], 'photoLng' => [null, '']]);

    expect($incident->refresh()->validated_locations)->toBe([]);

    Http::assertNothingSent();
});
