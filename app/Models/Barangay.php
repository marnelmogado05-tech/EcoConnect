<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Barangay extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'municipality_id'
    ];

    /**
     * A barangay belongs to one municipality. This was named municipalities(), plural,
     * which reads as a has-many and made call sites hard to trust.
     */
    public function municipality(): BelongsTo
    {
        return $this->belongsTo(Municipality::class);
    }
}
