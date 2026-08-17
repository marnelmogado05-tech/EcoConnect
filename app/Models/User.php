<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Events\UserRegistered;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Policies\UserPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[UsePolicy(UserPolicy::class)]
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
        'id_card_path',
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
        // Never expose where a user's identity document is stored.
        'id_card_path',
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
            'role' => UserRole::class,
            'status' => UserStatus::class,
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
            if ($user->role === UserRole::Citizen) {
                UserRegistered::dispatch($user);
            }
        });
    }

    /**
     * The user's display name.
     *
     * Names are stored as separate columns, so there is no `name` field. Notification
     * listeners and the incident export nevertheless read $user->name, which silently
     * resolved to null — producing "reported by Unknown" on every push notification and a
     * blank Assigned To column in every export.
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => trim(implode(' ', array_filter([
            $this->fname,
            $this->lname,
            $this->extname,
        ]))));
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'user_id');
    }

    /**
     * These four were each an if/else around a boolean expression comparing a magic
     * string. With the role cast to an enum they collapse to one comparison apiece.
     */
    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(UserRole::Admin);
    }

    public function isPolice(): bool
    {
        return $this->hasRole(UserRole::Police);
    }

    public function isBFP(): bool
    {
        return $this->hasRole(UserRole::Bfp);
    }

    public function isUser(): bool
    {
        return $this->hasRole(UserRole::Citizen);
    }

    /**
     * Police and BFP accounts, which act on incidents assigned to them.
     */
    public function isResponder(): bool
    {
        return $this->role?->isResponder() ?? false;
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

    public function pushSubscriptions(): HasMany
    {
        return $this->hasMany(PushSubscription::class);
    }
}
