<?php

namespace App\Jobs;

use App\Models\Incident;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidateIncidentLocation implements ShouldQueue
{
    use Queueable;

    protected $incident;
    protected $photosLatitude;
    protected $photosLongitude;

    /**
     * Create a new job instance.
     */
    public function __construct(Incident $incident, $photosLatitude = [], $photosLongitude = [])
    {
        $this->incident = $incident;
        $this->photosLatitude = $photosLatitude;
        $this->photosLongitude = $photosLongitude;
    }

    /**
     * Execute the job - validate incident location against allowed municipalities.
     */
    public function handle(): void
    {
        $allowedMunicipalities = [
            'Sta Praxedes', 'Claveria', 'Sanchez Mira',
            'Pamplona', 'Abulug', 'Ballesteros', 'Calayan'
        ];

        $locationIsValid = false;
        $validatedLocations = [];

        if (!empty($this->photosLatitude) && !empty($this->photosLongitude)) {
            foreach ($this->photosLatitude as $index => $lat) {
                $lon = $this->photosLongitude[$index] ?? null;

                if ($lat && $lon) {
                    try {
                        $response = Http::withHeaders([
                            'User-Agent' => 'EcoConnect/1.0',
                        ])->timeout(5)
                        ->connectTimeout(3)
                        ->get('https://nominatim.openstreetmap.org/reverse', [
                            'format' => 'jsonv2',
                            'lat' => $lat,
                            'lon' => $lon,
                            'addressdetails' => 1,
                        ]);

                        if ($response->successful()) {
                            $address = $response->json('address');
                            $municipality = $address['city']
                                ?? $address['town']
                                ?? $address['municipality']
                                ?? $address['county']
                                ?? null;

                            $isInAllowedArea = $municipality &&
                                in_array(trim($municipality), $allowedMunicipalities);

                            $validatedLocations[] = [
                                'latitude' => $lat,
                                'longitude' => $lon,
                                'municipality' => $municipality,
                                'is_valid' => $isInAllowedArea,
                            ];

                            if ($isInAllowedArea) {
                                $locationIsValid = true;
                            }
                        }
                    } catch (\Exception $e) {
                        Log::warning("Location validation failed for coordinates [$lat, $lon]: " . $e->getMessage());
                    }
                }
            }
        }

        // Update incident with validation results
        $this->incident->update([
            'location_validated' => true,
            'location_is_valid' => $locationIsValid,
            'validated_locations' => json_encode($validatedLocations),
        ]);

        Log::info("Incident #{$this->incident->id} location validation complete. Valid: {$locationIsValid}");
    }
}
