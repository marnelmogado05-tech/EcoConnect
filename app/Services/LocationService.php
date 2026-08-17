<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reverse-geocodes coordinates through Nominatim.
 *
 * Every method here reaches the network, so nothing in this class belongs in a request.
 * It is called from queued jobs only; the resolved values are written to the incident
 * and read from there afterwards.
 */
class LocationService
{
    /**
     * How long a resolved coordinate pair stays cached.
     */
    private const CACHE_TTL = 86400;

    /**
     * Resolve a coordinate pair to a municipality name and a formatted address.
     *
     * One request serves both, where the old code made separate calls for the
     * municipality and the address of the same point.
     *
     * @return array{municipality: ?string, address: ?string}
     */
    public function resolve(float|string|null $latitude, float|string|null $longitude): array
    {
        $empty = ['municipality' => null, 'address' => null];

        if (! $this->isValidCoordinates($latitude, $longitude)) {
            return $empty;
        }

        $cacheKey = 'geocode:'.round((float) $latitude, 5).','.round((float) $longitude, 5);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($latitude, $longitude, $empty) {
            try {
                $response = Http::timeout(10)
                    ->connectTimeout(5)
                    ->withHeaders([
                        'User-Agent' => 'EcoConnect/1.0',
                        'Accept' => 'application/json',
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'format' => 'jsonv2',
                        'addressdetails' => 1,
                        'zoom' => 18,
                    ]);

                if (! $response->successful()) {
                    Log::warning('Geocoding API responded with status '.$response->status());

                    return $empty;
                }

                $address = $response->json('address') ?? [];

                return [
                    'municipality' => $this->extractMunicipality($address),
                    'address' => $this->formatAddress($address),
                ];
            } catch (\Throwable $e) {
                Log::error("Geocoding failed for {$latitude},{$longitude}: ".$e->getMessage());

                return $empty;
            }
        });
    }

    /**
     * Municipality name only.
     */
    public function getMunicipalityFromCoordinates(float|string|null $latitude, float|string|null $longitude): ?string
    {
        return $this->resolve($latitude, $longitude)['municipality'];
    }

    private function isValidCoordinates(float|string|null $latitude, float|string|null $longitude): bool
    {
        if (! is_numeric($latitude) || ! is_numeric($longitude)) {
            return false;
        }

        $lat = (float) $latitude;
        $lon = (float) $longitude;

        return $lat >= -90 && $lat <= 90 && $lon >= -180 && $lon <= 180;
    }

    /**
     * @param  array<string, mixed>  $address
     */
    private function extractMunicipality(array $address): ?string
    {
        $name = $address['municipality']
            ?? $address['city']
            ?? $address['town']
            ?? $address['village']
            ?? $address['county']
            ?? null;

        // Nominatim sometimes qualifies a name, e.g. "Pamplona, Cagayan".
        return $name === null ? null : trim(explode(',', $name)[0]);
    }

    /**
     * Build a human-readable address from the response components.
     *
     * @param  array<string, mixed>  $address
     */
    private function formatAddress(array $address): ?string
    {
        $parts = array_filter([
            $address['house_number'] ?? null,
            $address['road'] ?? $address['footway'] ?? null,
            $address['neighbourhood'] ?? $address['suburb'] ?? $address['village'] ?? null,
            $address['city'] ?? $address['town'] ?? $address['municipality'] ?? $address['county'] ?? null,
            $address['state'] ?? null,
            $address['postcode'] ?? null,
        ]);

        return $parts === [] ? null : implode(', ', $parts);
    }
}
