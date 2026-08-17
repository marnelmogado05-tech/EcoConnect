<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Models\MediaEvidence;
use App\Models\Municipality;
use App\Services\LocationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

/**
 * Resolves where an incident happened, once, and writes it to the row.
 *
 * This replaces reverse-geocoding performed inline while rendering. The admin incident
 * list, the municipality filter dropdown and the analytics page each called Nominatim
 * per incident — or per distinct coordinate pair — with sleep(1) or usleep(200000)
 * between calls to stay inside the rate limit. Filtering by municipality could take
 * minutes, and the result was thrown away at the end of the request.
 *
 * Also records the municipality against the covered-area list, which is what
 * location_is_valid means.
 */
class ResolveIncidentLocation implements ShouldQueue
{
    use Queueable;

    /**
     * Municipalities the platform covers. Kept in step with MunicipalitySeeder.
     */
    public const COVERED_MUNICIPALITIES = [
        'Sta Praxedes',
        'Claveria',
        'Sanchez Mira',
        'Pamplona',
        'Abulug',
        'Ballesteros',
        'Calayan',
    ];

    public function __construct(
        protected Incident $incident,
    ) {
        // The job reads the media rows written in the same transaction as the incident,
        // so it must not start until that transaction commits.
        $this->afterCommit();
    }

    public function handle(LocationService $locations): void
    {
        $located = $this->incident->mediaEvidence()
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        if ($located->isEmpty()) {
            $this->incident->update([
                'location_validated' => true,
                'location_is_valid' => false,
                'validated_locations' => [],
            ]);

            return;
        }

        $validated = [];
        $incidentMunicipality = null;
        $incidentAddress = null;

        foreach ($located as $media) {
            $resolved = $locations->resolve($media->latitude, $media->longitude);

            // Cache the address on the media row so the accessor never reaches the
            // network while a template is rendering.
            $media->forceFill([
                'address' => $resolved['address'],
                'geocoded_at' => now(),
            ])->save();

            $isCovered = $resolved['municipality'] !== null
                && in_array($resolved['municipality'], self::COVERED_MUNICIPALITIES, true);

            $validated[] = [
                'latitude' => (float) $media->latitude,
                'longitude' => (float) $media->longitude,
                'municipality' => $resolved['municipality'],
                'is_valid' => $isCovered,
            ];

            // The first resolvable point stands for the incident.
            $incidentMunicipality ??= $resolved['municipality'];
            $incidentAddress ??= $resolved['address'];
        }

        $this->incident->update([
            'municipality_id' => $this->municipalityId($incidentMunicipality),
            'municipality_name' => $incidentMunicipality,
            'address' => $incidentAddress,
            'location_validated' => true,
            'location_is_valid' => collect($validated)->contains('is_valid', true),
            'validated_locations' => $validated,
        ]);

        Log::info("Incident #{$this->incident->id} location resolved.", [
            'municipality' => $incidentMunicipality,
            'points' => count($validated),
        ]);
    }

    /**
     * Match a resolved name to a seeded municipality, so the filter can join on an id.
     */
    private function municipalityId(?string $name): ?int
    {
        if ($name === null) {
            return null;
        }

        return Municipality::where('name', $name)->value('id');
    }
}
