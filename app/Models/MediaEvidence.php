<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class MediaEvidence extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'media_evidence';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'incident_id',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'latitude',
        'longitude',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_size' => 'integer',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the incident that owns the media evidence.
     */
    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * Scope a query to only include images.
     */
    public function scopeImages($query)
    {
        return $query->where('mime_type', 'like', 'image/%');
    }

    /**
     * Scope a query to only include videos.
     */
    public function scopeVideos($query)
    {
        return $query->where('mime_type', 'like', 'video/%');
    }

    /**
     * Scope a query to only include documents.
     */
    public function scopeDocuments($query)
    {
        return $query->whereNotIn('mime_type', [
            'image/jpeg', 'image/png', 'image/gif', 'image/webp',
            'video/mp4', 'video/avi', 'video/mov', 'video/webm'
        ]);
    }

    /**
     * Scope a query to only include media with GPS coordinates.
     */
    public function scopeWithLocation($query)
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    /**
     * Check if the media is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the media is a video.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if the media is a document.
     */
    public function isDocument(): bool
    {
        return !$this->isImage() && !$this->isVideo();
    }

    /**
     * Check if the media has GPS coordinates.
     */
    public function hasLocation(): bool
    {
        return !is_null($this->latitude) && !is_null($this->longitude);
    }

    /**
     * URL for displaying this file.
     *
     * Points at a route guarded by IncidentPolicy::viewMedia rather than at a public
     * asset path. Evidence lives on the private disk, so asset('storage/...') no longer
     * resolves to anything — which is the point.
     */
    protected function url(): Attribute
    {
        return Attribute::get(fn (): string => route('media.show', $this));
    }

    /**
     * Retained under its old name so existing callers keep working.
     */
    protected function fileUrl(): Attribute
    {
        return Attribute::get(fn (): string => $this->url);
    }

    /**
     * Absolute path on disk, for the rare case that needs the bytes locally
     * (image dimensions, embedding into an email).
     */
    protected function storagePath(): Attribute
    {
        return Attribute::get(fn (): string => Storage::disk('local')->path($this->file_path));
    }

    /**
     * Get human-readable file size.
     */
    protected function fileSizeHuman(): Attribute
    {
        return Attribute::make(
            get: function () {
                $bytes = $this->file_size;
                if ($bytes >= 1073741824) {
                    return number_format($bytes / 1073741824, 2) . ' GB';
                } elseif ($bytes >= 1048576) {
                    return number_format($bytes / 1048576, 2) . ' MB';
                } elseif ($bytes >= 1024) {
                    return number_format($bytes / 1024, 2) . ' KB';
                } else {
                    return $bytes . ' bytes';
                }
            },
        );
    }

    /**
     * Get file icon based on mime type.
     */
    protected function fileIcon(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->isImage()) return 'fa-image';
                if ($this->isVideo()) return 'fa-video';
                
                return match($this->mime_type) {
                    'application/pdf' => 'fa-file-pdf',
                    'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'fa-file-word',
                    'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'fa-file-excel',
                    default => 'fa-file',
                };
            },
        );
    }

    /**
     * Get file type category for display.
     */
    protected function fileTypeCategory(): Attribute
    {
        return Attribute::make(
            get: function () {
                if ($this->isImage()) return 'Image';
                if ($this->isVideo()) return 'Video';
                return 'Document';
            },
        );
    }

    /**
     * Get Google Maps URL for the location.
     */
    protected function googleMapsUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->hasLocation()) {
                    return null;
                }
                return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
            },
        );
    }

    /**
     * Get location coordinates as string.
     */
    protected function coordinates(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->hasLocation()) {
                    return null;
                }
                return "{$this->latitude}, {$this->longitude}";
            },
        );
    }

    /**
     * Get file extension.
     */
    protected function fileExtension(): Attribute
    {
        return Attribute::make(
            get: function () {
                return pathinfo($this->file_name, PATHINFO_EXTENSION);
            },
        );
    }

    /**
     * Check if file exists in storage.
     */
    public function fileExists(): bool
    {
        return file_exists($this->storage_path);
    }

    /**
     * Get the dimensions for images (if available).
     */
    public function getImageDimensions(): ?array
    {
        if (!$this->isImage() || !$this->fileExists()) {
            return null;
        }

        try {
            $dimensions = getimagesize($this->storage_path);
            return [
                'width' => $dimensions[0],
                'height' => $dimensions[1],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getAddressAttribute()
    {
        // If no coordinates, return early
        if (!$this->latitude || !$this->longitude) {
            return 'No coordinates';
        }

        try {
            $response = Http::withHeaders([
                'User-Agent' => 'EcoConnect/1.0',
            ])->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => $this->latitude,
                'lon' => $this->longitude,
                'addressdetails' => 1,
                'zoom' => 18,
            ]);

            if ($response->successful()) {
                $address = $response->json('address');
                
                // Build address components
                $street = $address['road'] ?? $address['footway'] ?? null;
                $houseNumber = $address['house_number'] ?? null;
                $barangay = $address['neighbourhood'] 
                    ?? $address['suburb'] 
                    ?? $address['village'] 
                    ?? null;
                $municipality = $address['city'] 
                    ?? $address['town'] 
                    ?? $address['municipality'] 
                    ?? $address['county'] 
                    ?? null;
                $province = $address['state'] ?? null;
                $postcode = $address['postcode'] ?? null;
                
                // Construct full address
                $fullAddress = [];
                if ($houseNumber) $fullAddress[] = $houseNumber;
                if ($street) $fullAddress[] = $street;
                if ($barangay) $fullAddress[] = $barangay;
                if ($municipality) $fullAddress[] = $municipality;
                if ($province) $fullAddress[] = $province;
                if ($postcode) $fullAddress[] = $postcode;
                
                return implode(', ', $fullAddress);
            } else {
                return 'Address not found';
            }
        } catch (\Exception $e) {
            return 'Error fetching address';
        }
    }
}