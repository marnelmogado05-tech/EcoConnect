# Firebase Push Notifications - Quick Start Guide

## 🚀 Quick Implementation Examples

### 1. Add Notification Button to Layout

Add to your main layout template (`resources/views/layouts/app.blade.php`):

```blade
<!-- Include the notification manager script -->
<script src="{{ asset('js/notification-manager.js') }}"></script>

<!-- Add button in navbar or settings -->
<div class="notification-settings">
    <button id="notificationToggle" class="btn btn-primary" onclick="toggleNotifications()">
        Enable Push Notifications
    </button>
</div>

<script>
async function toggleNotifications() {
    const status = await window.notificationManager.getSubscriptionStatus();
    
    if (status.enabled) {
        // User already has notifications - offer to disable
        const confirmed = confirm('Disable push notifications?');
        if (confirmed) {
            await window.notificationManager.unsubscribe();
            document.getElementById('notificationToggle').textContent = 'Enable Push Notifications';
        }
    } else {
        // Enable notifications
        const success = await window.notificationManager.requestPermissionAndSubscribe();
        if (success) {
            document.getElementById('notificationToggle').textContent = 'Disable Push Notifications';
            alert('Push notifications enabled successfully!');
        }
    }
}

// Check status on page load
document.addEventListener('DOMContentLoaded', async () => {
    const status = await window.notificationManager.getSubscriptionStatus();
    const btn = document.getElementById('notificationToggle');
    if (status.enabled) {
        btn.textContent = 'Disable Push Notifications';
    }
});
</script>
```

### 2. Trigger Notification When Updating Incident Status

In your controller (e.g., `IncidentController.php`):

```php
<?php

use App\Events\IncidentStatusChanged;
use App\Models\Incident;

class IncidentController extends Controller
{
    public function updateStatus(Incident $incident, Request $request)
    {
        $newStatus = $request->input('status');
        
        // Update incident
        $incident->update(['status' => $newStatus]);
        
        // Dispatch event - this will automatically send push notifications
        IncidentStatusChanged::dispatch($incident);
        
        return response()->json(['success' => true]);
    }
}
```

### 3. Send Custom Notifications

In any controller or job:

```php
<?php

use App\Jobs\SendPushNotification;

class PaymentController extends Controller
{
    public function processPayment(Request $request)
    {
        // Process payment...
        
        // Send notification
        SendPushNotification::dispatch(
            userId: auth()->id(),
            title: 'Payment Successful',
            body: 'Your payment of $100 has been processed',
            data: [
                'payment_id' => 12345,
                'type' => 'payment_success'
            ]
        );
        
        return response()->json(['success' => true]);
    }
}
```

### 4. Send Bulk Notifications to Multiple Users

```php
<?php

use App\Models\User;
use App\Jobs\SendPushNotification;

class AnnouncementController extends Controller
{
    public function sendAnnouncement(Request $request)
    {
        $announcement = $request->input('announcement');
        
        // Get all active users
        $users = User::where('status', 'active')->get();
        
        // Send to each user
        foreach ($users as $user) {
            SendPushNotification::dispatch(
                userId: $user->id,
                title: 'New Announcement',
                body: $announcement,
                data: ['type' => 'announcement']
            );
        }
        
        return response()->json(['success' => true, 'sent_to' => $users->count()]);
    }
}
```

### 5. Blade Snippet for Settings Page

Add to a settings or profile page:

```blade
@extends('layouts.app')

@section('content')
<div class="container">
    <h2>Notification Preferences</h2>
    
    <div class="card">
        <div class="card-body">
            <h5>Push Notifications</h5>
            
            <div id="notificationStatus">
                <p>Loading...</p>
            </div>
            
            <button id="toggleBtn" class="btn btn-primary mt-3" onclick="toggleNotifications()">
                Enable Notifications
            </button>
        </div>
    </div>
</div>

<script src="{{ asset('js/notification-manager.js') }}"></script>

<script>
async function updateStatus() {
    const status = await window.notificationManager.getSubscriptionStatus();
    const statusDiv = document.getElementById('notificationStatus');
    
    if (status.enabled) {
        statusDiv.innerHTML = `
            <div class="alert alert-success">
                ✓ Push notifications enabled
                <br>
                <small>Active on ${status.token_count} device(s)</small>
            </div>
        `;
        document.getElementById('toggleBtn').textContent = 'Disable Notifications';
    } else {
        statusDiv.innerHTML = `
            <div class="alert alert-warning">
                Push notifications are disabled
            </div>
        `;
        document.getElementById('toggleBtn').textContent = 'Enable Notifications';
    }
}

async function toggleNotifications() {
    const status = await window.notificationManager.getSubscriptionStatus();
    
    if (status.enabled) {
        await window.notificationManager.unsubscribe();
    } else {
        await window.notificationManager.requestPermissionAndSubscribe();
    }
    
    updateStatus();
}

// Load status on page load
document.addEventListener('DOMContentLoaded', updateStatus);
</script>
@endsection
```

---

## 🎯 Common Use Cases

### Incident Assignment Notification

```php
// In IncidentController or Job
$incident->update(['assigned_to' => $userId]);

SendPushNotification::dispatch(
    userId: $userId,
    title: 'Incident Assigned',
    body: "New incident at {$incident->location}",
    data: [
        'incident_id' => $incident->id,
        'type' => 'incident_assigned'
    ]
);
```

### Emergency Alert

```php
// Notify all admin users
$admins = User::where('role', 'admin')->get();

foreach ($admins as $admin) {
    SendPushNotification::dispatch(
        userId: $admin->id,
        title: '🚨 Emergency Alert',
        body: 'Critical incident reported',
        data: [
            'critical' => true,
            'type' => 'emergency'
        ]
    );
}
```

### Follow-up Required

```php
SendPushNotification::dispatch(
    userId: $userId,
    title: 'Follow-up Required',
    body: "Your incident report requires follow-up information",
    data: [
        'incident_id' => $incident->id,
        'action' => 'follow_up'
    ]
);
```

---

## 🧪 Testing

### Test Command

```bash
# Send test notification to user ID 5
php artisan notifications:test 5

# In another terminal, process the queue
php artisan queue:work
```

### Debug Notifications

```php
// In tinker
php artisan tinker

// Check user tokens
>>> \App\Models\FcmToken::where('user_id', 1)->get()

// Send direct test
>>> $service = new \App\Services\FirebaseNotificationService();
>>> $service->sendToUser(1, 'Test', 'Testing notifications');
```

---

## 📋 Checklist

- [ ] Firebase credentials are in `storage/app/firebase-auth.json`
- [ ] `FIREBASE_CREDENTIALS` is set in `.env`
- [ ] `fcm_tokens` table exists in database
- [ ] Queue is configured (`QUEUE_CONNECTION=database`)
- [ ] `notification-manager.js` is included in layout
- [ ] `sw.js` service worker exists in public folder
- [ ] Notification routes are added to `routes/web.php`
- [ ] `FirebaseNotificationService` is available for injection
- [ ] Test notification sends successfully

---

## 🔗 Useful Links

- [Kreait Laravel Firebase](https://github.com/kreait/laravel-firebase)
- [Firebase Admin SDK](https://firebase.google.com/docs/admin/setup)
- [Cloud Messaging Docs](https://firebase.google.com/docs/cloud-messaging)
- [Web Push API](https://developer.mozilla.org/en-US/docs/Web/API/Push_API)

---

**Created**: April 9, 2026  
**Package**: kreait/laravel-firebase v6.2+
