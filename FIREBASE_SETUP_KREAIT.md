# Firebase Cloud Messaging (Kreait) Push Notifications Setup Guide

## ✅ Implementation Complete

Your EcoConnect application is now set up with Firebase Cloud Messaging (FCM) push notifications using the `kreait/laravel-firebase` package.

---

## 🎯 Core Features Implemented

✅ Real-time incident status notifications  
✅ New user registration alerts for admins  
✅ Automatic token management  
✅ Queue-based asynchronous notification delivery  
✅ Device identification and token tracking  
✅ Service Worker integration for offline support

---

## 📋 What's Been Set Up

### Backend Components

**1. Database Model**
- `FcmToken` model - Stores user FCM subscription tokens
- Database table: `fcm_tokens`

**2. Services**
- `FirebaseNotificationService` - Handles all FCM operations
  - `sendToUser()` - Send notification to specific user
  - `sendToToken()` - Send to individual token
  - `sendToMultipleUsers()` - Batch notifications
  - `testConnection()` - Verify Firebase setup

**3. Controllers**
- `NotificationController` - REST API endpoints
  - `saveFcmToken()` - Register device token
  - `removeFcmToken()` - Remove specific token
  - `removeAllTokens()` - Unsubscribe all devices
  - `getNotificationStatus()` - Check notification status

**4. Jobs**
- `SendPushNotification` - Queued job for async delivery

**5. Listeners/Events**
- `SendIncidentStatusNotification` - Listens to `IncidentStatusChanged` event
- `SendNewUserNotification` - Listens to `UserRegistered` event

**6. Console Commands**
- `notifications:test {user_id}` - Send test notification to user

### Frontend Components

**JavaScript Files**
- `notification-manager.js` - Client-side notification management
- `sw.js` - Service Worker for offline support

**API Routes**
- `POST /api/notifications/save-token` - Save FCM token
- `POST /api/notifications/remove-token` - Remove token
- `POST /api/notifications/remove-all` - Remove all tokens
- `GET /api/notifications/status` - Get status

---

## 🚀 How to Use

### 1. Enable Notifications in Frontend

Add this to your layout blade template (e.g., `resources/views/layouts/app.blade.php`):

```blade
<!-- Include notification manager -->
<script src="{{ asset('js/notification-manager.js') }}"></script>

<!-- Button to enable notifications -->
<button onclick="window.notificationManager.requestPermissionAndSubscribe()">
    Enable Notifications
</button>
```

### 2. Check Notification Status

```javascript
// Check if user has notifications enabled
const status = await window.notificationManager.getSubscriptionStatus();
console.log('Notifications enabled:', status.enabled);
console.log('Active tokens:', status.token_count);
```

### 3. Disable Notifications

```javascript
// Unsubscribe user from notifications
await window.notificationManager.unsubscribe();
```

### 4. Send Test Notification

```bash
# Send test notification to user with ID 1
php artisan notifications:test 1

# Process the queue to send the notification
php artisan queue:work
```

---

## 📤 Sending Notifications Programmatically

### Manual Notification Sending

```php
use App\Services\FirebaseNotificationService;

$service = new FirebaseNotificationService();

// Send to single user
$service->sendToUser(
    userId: 1,
    title: 'Incident Update',
    body: 'Your incident has been assigned',
    data: ['incident_id' => 123]
);

// Send to multiple users
$service->sendToMultipleUsers(
    userIds: [1, 2, 3],
    title: 'System Alert',
    body: 'Important system notification'
);
```

### Via Jobs (Recommended - Asynchronous)

```php
use App\Jobs\SendPushNotification;

SendPushNotification::dispatch(
    userId: 1,
    title: 'Incident Status',
    body: 'Your incident has been updated',
    data: ['incident_id' => 123]
);
```

### Automatic Event-Based

Notifications are automatically sent for:

1. **Incident Status Changes** - When an incident status is updated
2. **New User Registration** - Admins are notified when users register

---

## 🔧 Configuration

### Environment Variables (.env)

```env
FIREBASE_CREDENTIALS=storage/app/firebase-auth.json
```

### Firebase Configuration (config/firebase.php)

The package automatically discovers and loads your Firebase credentials from the JSON file.

### Queue Configuration

Ensure your queue is configured properly in `.env`:

```env
QUEUE_CONNECTION=database
```

To process notifications, run:

```bash
php artisan queue:work
```

Or use a supervisor/cron to keep it running.

---

## 📚 Database Schema

### fcm_tokens Table

```
id              - Primary key
user_id         - Foreign key to users table
token           - Firebase subscription token
device_name     - Device identifier
is_active       - Whether token is active
created_at      - Creation timestamp
updated_at      - Update timestamp
```

---

## 🧪 Testing

### 1. Test Firebase Connection

```bash
php artisan tinker
>>> $service = new App\Services\FirebaseNotificationService();
>>> $service->testConnection();
```

### 2. Send Test Notification

```bash
php artisan notifications:test 1
php artisan queue:work
```

### 3. Check Tokens for User

```bash
php artisan tinker
>>> use App\Models\FcmToken;
>>> FcmToken::where('user_id', 1)->get();
```

---

## 🛡️ Security Considerations

1. **CSRF Protection** - All API endpoints require valid CSRF tokens
2. **Authentication** - All notification endpoints require authenticated users
3. **Token Isolation** - Users can only manage their own tokens
4. **Invalid Token Cleanup** - Invalid tokens are automatically deleted

---

## 🐛 Troubleshooting

### Notifications Not Sending

1. **Check Queue is Running**
   ```bash
   php artisan queue:work
   ```

2. **Check Logs**
   ```bash
   tail -f storage/logs/laravel.log
   ```

3. **Verify Firebase Credentials**
   ```bash
   php artisan tinker
   >>> config('firebase.projects.app.credentials')
   ```

4. **Check User Has Tokens**
   ```bash
   php artisan tinker
   >>> \App\Models\FcmToken::where('user_id', 1)->count()
   ```

### Service Worker Issues

1. Clear browser cache
2. Verify Service Worker is registered: Check DevTools → Application → Service Workers
3. Check browser console for errors

### CORS Issues

1. Ensure your frontend is making requests to the same domain
2. Check CSRF token is included in headers

---

## 📖 API Reference

### POST /api/notifications/save-token

Save a new FCM token

**Request:**
```json
{
    "token": "subscription_token_string",
    "device_name": "Mobile Chrome"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Notification token saved successfully",
    "token_id": 1
}
```

### POST /api/notifications/remove-token

Remove specific token

**Request:**
```json
{
    "token": "subscription_token_string"
}
```

### POST /api/notifications/remove-all

Remove all tokens for current user

**Response:**
```json
{
    "success": true,
    "message": "All notification tokens removed successfully"
}
```

### GET /api/notifications/status

Get current user's notification status

**Response:**
```json
{
    "success": true,
    "enabled": true,
    "token_count": 2
}
```

---

## 📞 Support

For more information, refer to:
- [Kreait Laravel Firebase Documentation](https://github.com/kreait/laravel-firebase)
- [Firebase Cloud Messaging Docs](https://firebase.google.com/docs/cloud-messaging)
- Laravel documentation on Queues and Events

---

## 🔄 Workflow Example

1. User visits application and clicks "Enable Notifications"
2. Browser prompts for notification permission
3. User grants permission
4. JavaScript subscribes to push notifications and sends token to server
5. Server stores token in `fcm_tokens` table
6. When incident status changes:
   - `IncidentStatusChanged` event is fired
   - `SendIncidentStatusNotification` listener catches it
   - `SendPushNotification` job is queued
   - Queue worker processes the job
   - Firebase notification is sent to user's devices
   - User receives notification on registered devices

---

Last Updated: April 9, 2026
