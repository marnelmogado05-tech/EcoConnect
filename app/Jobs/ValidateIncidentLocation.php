<?php

namespace App\Jobs;

use App\Models\Incident;
use App\Services\LocationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class ValidateIncidentLocation implements ShouldQueue
{
    use Queueable;

    /**
     * Municipalities the platform covers. Kept in step with MunicipalitySeeder.
     */
    public const ALLOWED_MUNICIPALITIES = [
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
        protected array $photosLatitude = [],
        protected array $photosLongitude = [],
        protected array $videosLatitude = [],
        protected array $videosLongitude = [],
    ) {
    }

    /**
     * Resolve each supplied coordinate pair and record whether any of them falls inside
     * the covered municipalities.
     */
    public function handle(LocationService $locations): void
    {
        $validatedLocations = [];
        $locationIsValid = false;

        foreach ($this->coordinatePairs() as [$latitude, $longitude]) {
            try {
                $municipality = $locations->getMunicipalityFromCoordinates($latitude, $longitude);
            } catch (\Throwable $e) {
                Log::warning("Location validation failed for [{$latitude}, {$longitude}]: ".$e->getMessage());

                continue;
            }

            $isInAllowedArea = $municipality !== null
                && in_array(trim($municipality), self::ALLOWED_MUNICIPALITIES, true);

            $validatedLocations[] = [
                'latitude' => $latitude,
                'longitude' => $longitude,
                'municipality' => $municipality,
                'is_valid' => $isInAllowedArea,
            ];

            $locationIsValid = $locationIsValid || $isInAllowedArea;
        }

        // These three columns were absent from Incident::$fillable, so this update
        // discarded every one of them without raising anything. The job logged success
        // and wrote nothing.
        $this->incident->update([
            'location_validated' => true,
            'location_is_valid' => $locationIsValid,
            'validated_locations' => $validatedLocations,
        ]);

        Log::info("Incident #{$this->incident->id} location validation complete.", [
            'coordinates_checked' => count($validatedLocations),
            'is_valid' => $locationIsValid,
        ]);
    }

    /**
     * Photo and video coordinates, paired up and filtered to usable values.
     *
     * The video arrays were previously dropped on the floor: the controller passed five
     * arguments to a three-argument constructor, so a report backed only by video was
     * never location-checked at all.
     *
     * @return list<array{0: float, 1: float}>
     */
    private function coordinatePairs(): array
    {
        $pairs = [];

        $sources = [
            [$this->photosLatitude, $this->photosLongitude],
            [$this->videosLatitude, $this->videosLongitude],
        ];

        foreach ($sources as [$latitudes, $longitudes]) {
            foreach ($latitudes as $index => $latitude) {
                $longitude = $longitudes[$index] ?? null;

                if (! is_numeric($latitude) || ! is_numeric($longitude)) {
                    continue;
                }

                $pairs[] = [(float) $latitude, (float) $longitude];
            }
        }

        return $pairs;
    }
}
