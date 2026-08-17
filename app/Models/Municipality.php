<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    public function barangays()
    {
        return $this->hasMany(Barangay::class);
    }

    /**
     * Users based in this municipality.
     *
     * This was a hasOne named police() with no role constraint, so it returned whichever
     * user happened to come first — citizen, admin or officer.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Responding officers based in this municipality.
     */
    public function responders(): HasMany
    {
        return $this->users()->whereIn('role', UserRole::responderValues());
    }
}
