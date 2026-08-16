<?php

namespace App\Models;

use App\Observers\IncidentObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[ObservedBy([IncidentObserver::class])]
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
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'incident_date' => 'date',
        'resolved_date' => 'date',
        'assigned_at' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'date_taken_into_action' => 'datetime',
        'location_validated' => 'boolean',
        'location_is_valid' => 'boolean',
        'validated_locations' => 'array',
    ];

    /**
     * Incident type constants
     */
    const TYPE_ILLEGAL_LOGGING = 'Illegal Logging';
    const TYPE_POLLUTION = 'Pollution';
    const TYPE_WILDLIFE_CRIME = 'Wildlife Crime';
    const TYPE_ILLEGAL_WASTE_DISPOSAL = 'Illegal Waste Disposal';
    const TYPE_OTHER = 'Other';

    /**
     * Status constants
     */
    const STATUS_PENDING = 'Pending';
    const STATUS_IN_PROGRESS = 'In Progress';
    const STATUS_RESOLVED = 'Resolved';
    const STATUS_REJECTED = 'Rejected';

    /**
     * Priority constants
     */
    const PRIORITY_NORMAL = 'Normal';
    const PRIORITY_HIGH = 'High';
    const PRIORITY_URGENT = 'Urgent';


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
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope a query to only include in progress incidents.
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    /**
     * Scope a query to only include resolved incidents.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    /**
     * Scope a query to only include rejected incidents.
     */
    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
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
        return $query->where('priority', self::PRIORITY_HIGH);
    }

    /**
     * Scope a query to only include urgent priority incidents.
     */
    public function scopeUrgentPriority($query)
    {
        return $query->where('priority', self::PRIORITY_URGENT);
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
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if incident is in progress.
     */
    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    /**
     * Check if incident is resolved.
     */
    public function isResolved(): bool
    {
        return $this->status === self::STATUS_RESOLVED;
    }

    /**
     * Check if incident is rejected.
     */
    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
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
        return $this->priority === self::PRIORITY_HIGH;
    }

    /**
     * Check if incident is urgent priority.
     */
    public function isUrgentPriority(): bool
    {
        return $this->priority === self::PRIORITY_URGENT;
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
     */
    public static function getIncidentTypes(): array
    {
        return [
            self::TYPE_ILLEGAL_LOGGING => 'Illegal Logging',
            self::TYPE_POLLUTION => 'Pollution',
            self::TYPE_WILDLIFE_CRIME => 'Wildlife Crime',
            self::TYPE_ILLEGAL_WASTE_DISPOSAL => 'Illegal Waste Disposal',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get available statuses for forms.
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pending',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_REJECTED => 'Rejected',
        ];
    }

    /**
     * Get available priorities for forms.
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_NORMAL => 'Normal',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent',
        ];
    }

    /**
     * Assign the incident to a staff member.
     */
    public function assignTo(User $user): void
    {
        $this->update([
            'assigned_to' => $user->id,
            'assigned_at' => now(),
            'status' => self::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Mark incident as resolved.
     */
    public function markAsResolved(string $resolutionDetails): void
    {
        $this->update([
            'status' => self::STATUS_RESOLVED,
            'resolution_details' => $resolutionDetails,
            'resolved_date' => now(),
        ]);
    }

    /**
     * Reject incident with reason.
     */
    public function reject(string $reason): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Update priority level.
     */
    public function updatePriority(string $priority): void
    {
        $this->update(['priority' => $priority]);
    }

    public function acknowledgement()
    {
        return $this->hasOne(IncidentAcknowledgement::class);
    }

    public function getDocumentationAcknowledgement()
    {
        return $this->acknowledgements()->first();
    }

    public function followups(): HasMany {
        return $this->hasMany(IncidentFollowup::class)->orderByDesc('created_at');
    }
}
