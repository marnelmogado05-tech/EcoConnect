<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * A browser push subscription.
 *
 * Formerly FcmToken. Nothing here has ever been an FCM token: `subscription` holds the
 * JSON document the browser's PushManager returns — an endpoint plus the p256dh and auth
 * keys — which minishlink/web-push needs to deliver a notification.
 */
class PushSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'subscription',
        'device_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * The subscription document is never useful outside the push service, and it
     * identifies a specific browser install.
     */
    protected $hidden = [
        'subscription',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Active subscriptions for a user.
     */
    public function scopeActiveFor($query, int $userId)
    {
        return $query->where('user_id', $userId)->where('is_active', true);
    }
}
