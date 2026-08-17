<?php

namespace App\Models;

use App\Enums\IncidentPriority;
use App\Enums\IncidentStatus;
use App\Enums\IncidentType;
use App\Observers\IncidentObserver;
use App\Policies\IncidentPolicy;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[ObservedBy([IncidentObserver::class])]
#[UsePolicy(IncidentPolicy::class)]
class Incident extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'reference_number',
        'title',
        'user_id',
        'incident_type',
        'description',
        'incident_date',
        'incident_time',
        'status',
        'priority',
        'rejection_reason',
        'resolution_details',
        'resolved_date',
        'assigned_to',
        'assigned_at',
        'date_taken_into_action',
        'location_validated',
        'location_is_valid',
        'validated_locations',
        'municipality_id',
        'municipality_name',
        'address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => IncidentStatus::class,
        'priority' => IncidentPriority::class,
        'incident_type' => IncidentType::class,
        'incident_date' => 'date',
        'resolved_date' => 'date',
        // Was cast as 'date' while every writer passes now(), silently discarding the
        // time an incident was assigned.
        'assigned_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date_taken_into_action' => 'datetime',
        'location_validated' => 'boolean',
        'location_is_valid' => 'boolean',
        'validated_locations' => 'array',
    ];

    /**
     * Generate unique reference number
     */
    public static function generateReferenceNumber(): string
    {
        $prefix = 'DENR';
        $year = date('Y');
        $random = Str::upper(Str::random(6));

        $reference = "{$prefix}-{$year}-{$random}";

        // Ensure uniqueness
        while (self::where('reference_number', $reference)->exists()) {
            $random = Str::upper(Str::random(6));
            $reference = "{$prefix}-{$year}-{$random}";
        }

        return $reference;
    }

    /**
     * Get the user who reported the incident.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the staff assigned to handle the incident.
     */
    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get all media evidence for the incident.
     */
    public function mediaEvidence(): HasMany
    {
        return $this->hasMany(MediaEvidence::class);
    }

    /**
     * Get primary media evidence (first image if available).
     */
    public function getPrimaryMediaAttribute()
    {
        return $this->mediaEvidence->first(function ($media) {
            return $media->isImage();
        }) ?? $this->mediaEvidence->first();
    }

    /**
     * Check if incident has media evidence.
     */
    public function hasMedia(): bool
    {
        return $this->mediaEvidence->count() > 0;
    }

    /**
     * Get only image evidence.
     */
    public function getImagesAttribute()
    {
        return $this->mediaEvidence->filter(function ($media) {
            return $media->isImage();
        });
    }

    /**
     * Get only video evidence.
     */
    public function getVideosAttribute()
    {
        return $this->mediaEvidence->filter(function ($media) {
            return $media->isVideo();
        });
    }

    /**
     * Get media evidence with location data.
     */
    public function getMediaWithLocationAttribute()
    {
        return $this->mediaEvidence->filter(function ($media) {
            return $media->hasLocation();
        });
    }

    /**
     * Scope a query to only include incidents of a specific status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include pending incidents.
     */
    public function scopePending($query)
    {
        return $query->where('status', IncidentStatus::Pending);
    }

    /**
     * Scope a query to only include in progress incidents.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', IncidentStatus::InProgress);
    }

    /**
     * Scope a query to only include resolved incidents.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', IncidentStatus::Resolved);
    }

    /**
     * Scope a query to only include rejected incidents.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', IncidentStatus::Rejected);
    }

    /**
     * Scope a query to only include incidents of a specific type.
     */
    public function scopeType($query, $type)
    {
        return $query->where('incident_type', $type);
    }

    /**
     * Scope a query to only include high priority incidents.
     */
    public function scopeHighPriority($query)
    {
        return $query->where('priority', IncidentPriority::High);
    }

    /**
     * Scope a query to only include urgent priority incidents.
     */
    public function scopeUrgentPriority($query)
    {
        return $query->where('priority', IncidentPriority::Urgent);
    }

    /**
     * Scope a query to only include recent incidents.
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('incident_date', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to only include unassigned incidents.
     */
    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    /**
     * Check if incident is pending.
     */
    public function isPending(): bool
    {
        return $this->status === IncidentStatus::Pending;
    }

    /**
     * Check if incident is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === IncidentStatus::InProgress;
    }

    /**
     * Check if incident is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === IncidentStatus::Resolved;
    }

    /**
     * Check if incident is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === IncidentStatus::Rejected;
    }

    /**
     * Check if incident is assigned.
     */
    public function isAssigned(): bool
    {
        return !is_null($this->assigned_to);
    }

    /**
     * Check if incident is high priority.
     */
    public function isHighPriority(): bool
    {
        return $this->priority === IncidentPriority::High;
    }

    /**
     * Check if incident is urgent priority.
     */
    public function isUrgentPriority(): bool
    {
        return $this->priority === IncidentPriority::Urgent;
    }

    /**
     * Get the incident date and time as a DateTime object.
     */
    public function getIncidentDateTimeAttribute()
    {
        return \Carbon\Carbon::parse($this->incident_date->format('Y-m-d') . ' ' . $this->incident_time);
    }

    /**
     * Get the time elapsed since the incident was reported.
     */
    public function getTimeElapsedAttribute(): string
    {
        return $this->created_at->diffForHumans();
    }

    /**
     * Get the incident age in days.
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at->diffInDays();
    }

    /**
     * Get available incident types for forms.
     *
     * @return array<string, string>
     */
    public static function getIncidentTypes(): array
    {
        return IncidentType::options();
    }

    /*
     * assignTo(), markAsResolved(), reject() and updatePriority() used to live here.
     * Every one of them was written and called by nothing — each controller
     * reimplemented the same update inline, with slightly different behaviour. State
     * transitions now belong to IncidentWorkflowService, which is the only thing that
     * moves an incident between statuses.
     */

    /**
     * Where the incident happened, resolved once by ResolveIncidentLocation.
     */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }

    public function acknowledgement(): HasOne
    {
        return $this->hasOne(IncidentAcknowledgement::class);
    }

    /**
     * The relation is acknowledgement(), singular. This called acknowledgements() and
     * threw a BadMethodCallException on sight.
     */
    public function getDocumentationAcknowledgement(): ?IncidentAcknowledgement
    {
        return $this->acknowledgement()->first();
    }

    public function followups(): HasMany
    {
        return $this->hasMany(IncidentFollowup::class)->orderByDesc('created_at');
    }
}
