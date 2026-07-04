<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\UserRegistered;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'fname',
        'lname',
        'mname',
        'extname',
        'phone',
        'role',
        'status',
        'barangay_id',
        'municipality_id',
        'id_card',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot function to dispatch UserRegistered event
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($user) {
            // Only dispatch event for regular users, not for admins/staff created via admin panel
            if ($user->role === 'user') {
                UserRegistered::dispatch($user);
            }
        });
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'user_id');
    }

    public function isAdmin(): bool
    {
        if($this->role === 'admin'){
            return true;
        } else {
            return false;
        }
    }

    public function isPolice(): bool
    {
        if($this->role === 'police'){
            return true;
        } else {
            return false;
        }
    }

    public function isBFP(): bool
    {
        if($this->role === 'bfp'){
            return true;
        } else {
            return false;
        }
    }

    public function isUser(): bool
    {
        if($this->role === 'user'){
            return true;
        } else {
            return false;
        }
    }

    public function barangay()
    {
        return $this->belongsTo(Barangay::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function assignedIncidents()
    {
        return $this->hasMany(Incident::class, 'assigned_to');
    }

    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class);
    }
}
