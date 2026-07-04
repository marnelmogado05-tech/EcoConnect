<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class IncidentFollowup extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'user_id',
        'follow_up_text',
        'follow_up_type',
        'is_read',
    ];

    public function incident(): BelongsTo {
        return $this->belongsTo(Incident::class);
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
