<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IncidentAcknowledgement extends Model
{
    protected $fillable = [
        'incident_id',
        'officer_id',
        'acknowledged_at',
        'documentation',
        'evidence_count',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function incident(): BelongsTo
    {
        return $this->belongsTo(Incident::class);
    }

    public function officer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'officer_id');
    }

    public function hasDocumentation(): bool
    {
        return $this->acknowledged_at !== null;
    }
}
