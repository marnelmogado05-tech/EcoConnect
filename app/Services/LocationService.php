<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class LocationService
{
    public function getMunicipalityFromCoordinates($latitude, $longitude)
    {
        // Validate coordinates
        if (!$this->isValidCoordinates($latitude, $longitude)) {
            return null;
        }

        // Cache key for these coordinates
        $cacheKey = "municipality_{$latitude}_{$longitude}";

        return Cache::remember($cacheKey, 86400, function () use ($latitude, $longitude) {
            try {
                // Using Nominatim (OpenStreetMap) - Free service
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'EcoConnect/1.0',
                        'Accept' => 'application/json'
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'format' => 'json',
                        'addressdetails' => 1,
                        'zoom' => 10,
                        'namedetails' => 0
                    ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->extractMunicipality($data);
                } else {
                    Log::warning("Geocoding API responded with error: " . $response->status());
                }
            } catch (\Exception $e) {
                Log::error("Geocoding failed for {$latitude},{$longitude}: " . $e->getMessage());
            }

            return null;
        });
    }

    private function isValidCoordinates($latitude, $longitude)
    {
        if (!is_numeric($latitude) || !is_numeric($longitude)) {
            return false;
        }

        $lat = floatval($latitude);
        $lon = floatval($longitude);

        return ($lat >= -90 && $lat <= 90) && ($lon >= -180 && $lon <= 180);
    }

    private function extractMunicipality($data)
    {
        $address = $data['address'] ?? [];

        // Try different address components that might contain municipality name
        // Priority order for municipality detection
        return $address['municipality'] ??
               $address['city'] ??
               $address['town'] ??
               $address['village'] ??
               $address['county'] ??
               $address['state_district'] ??
               $address['state'] ??
               null;
    }

    /**
     * Get full address details for more information
     */
    public function getFullAddressFromCoordinates($latitude, $longitude)
    {
        if (!$this->isValidCoordinates($latitude, $longitude)) {
            return null;
        }

        $cacheKey = "full_address_{$latitude}_{$longitude}";

        return Cache::remember($cacheKey, 86400, function () use ($latitude, $longitude) {
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'User-Agent' => 'EcoConnect/1.0',
                        'Accept' => 'application/json'
                    ])
                    ->get('https://nominatim.openstreetmap.org/reverse', [
                        'lat' => $latitude,
                        'lon' => $longitude,
                        'format' => 'json',
                        'addressdetails' => 1,
                        'zoom' => 10
                    ]);

                if ($response->successful()) {
                    return $response->json();
                }
            } catch (\Exception $e) {
                Log::error("Full address geocoding failed: " . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Clear cache for specific coordinates
     */
    public function clearCache($latitude, $longitude)
    {
        $cacheKey = "municipality_{$latitude}_{$longitude}";
        Cache::forget($cacheKey);

        $fullAddressKey = "full_address_{$latitude}_{$longitude}";
        Cache::forget($fullAddressKey);
    }
}
