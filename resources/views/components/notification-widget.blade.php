@if(auth()->check())
<div class="notification-widget" id="notification-widget">
    <button class="btn btn-sm" id="notification-toggle-btn" onclick="toggleNotificationWidget(event)">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
        </svg>
        <span id="notification-status-badge" class="badge bg-secondary">Off</span>
    </button>

    <div class="notification-dropdown" id="notification-dropdown" style="display: none;">
        <div class="notification-header">
            <h6>Notifications</h6>
        </div>
        <div class="notification-body">
            <div id="notification-status-info">
                <p class="text-muted">Loading...</p>
            </div>
        </div>
        <div class="notification-footer">
            <button class="btn btn-sm btn-primary w-100" id="notification-action-btn" onclick="handleNotificationAction()">
                Enable
            </button>
        </div>
    </div>
</div>

<style>
    .notification-widget {
        position: relative;
        display: inline-block;
    }

    .notification-widget .btn {
        position: relative;
        padding: 8px 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .notification-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        mt: 10px;
        background: white;
        border: 1px solid #ddd;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        width: 300px;
        z-index: 1000;
    }

    .notification-header {
        padding: 12px;
        border-bottom: 1px solid #eee;
        font-weight: 600;
    }

    .notification-body {
        padding: 12px;
        min-height: 80px;
        max-height: 300px;
        overflow-y: auto;
    }

    .notification-footer {
        padding: 12px;
        border-top: 1px solid #eee;
    }

    .badge {
        font-size: 10px;
        padding: 2px 6px;
    }
</style>

<script>
    async function toggleNotificationWidget(event) {
        event.stopPropagation();
        const dropdown = document.getElementById('notification-dropdown');
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';

        if (dropdown.style.display === 'block') {
            await updateNotificationStatus();
        }
    }

    async function updateNotificationStatus() {
        try {
            if (!window.notificationManager) {
                console.warn('NotificationManager not initialized');
                return;
            }

            const status = await window.notificationManager.getSubscriptionStatus();
            const statusInfo = document.getElementById('notification-status-info');
            const actionBtn = document.getElementById('notification-action-btn');
            const badge = document.getElementById('notification-status-badge');

            if (status && status.enabled) {
                statusInfo.innerHTML = `
                    <div class="alert alert-success mb-0">
                        <strong>✓ Enabled</strong><br>
                        <small>Active on ${status.token_count || 1} device(s)</small>
                    </div>
                `;
                actionBtn.textContent = 'Disable';
                actionBtn.classList.remove('btn-primary');
                actionBtn.classList.add('btn-danger');
                badge.textContent = 'On';
                badge.classList.remove('bg-secondary');
                badge.classList.add('bg-success');
            } else {
                statusInfo.innerHTML = `
                    <div class="alert alert-warning mb-0">
                        <strong>⚠ Disabled</strong><br>
                        <small>Click below to enable</small>
                    </div>
                `;
                actionBtn.textContent = 'Enable';
                actionBtn.classList.remove('btn-danger');
                actionBtn.classList.add('btn-primary');
                badge.textContent = 'Off';
                badge.classList.add('bg-secondary');
                badge.classList.remove('bg-success');
            }
        } catch (error) {
            console.error('Error updating notification status:', error);
            const statusInfo = document.getElementById('notification-status-info');
            statusInfo.innerHTML = `<div class="alert alert-danger mb-0">Error loading status</div>`;
        }
    }

    async function handleNotificationAction() {
        try {
            if (!window.notificationManager) {
                alert('Notification system loading. Please try again in a moment.');
                return;
            }

            const status = await window.notificationManager.getSubscriptionStatus();

            if (status && status.enabled) {
                const confirmed = confirm('Disable push notifications?');
                if (confirmed) {
                    const result = await window.notificationManager.unsubscribe();
                    if (result) {
                        await updateNotificationStatus();
                    }
                }
            } else {
                const success = await window.notificationManager.requestPermissionAndSubscribe();
                if (success) {
                    await updateNotificationStatus();
                    alert('Push notifications enabled successfully!');
                }
            }
        } catch (error) {
            console.error('Error handling notification action:', error);
            alert('Error: ' + error.message);
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(event) {
        const widget = document.getElementById('notification-widget');
        const dropdown = document.getElementById('notification-dropdown');
        if (widget && !widget.contains(event.target)) {
            dropdown.style.display = 'none';
        }
    });

    // Update status on page load
    document.addEventListener('DOMContentLoaded', async function() {
        // Wait for notificationManager to be initialized
        let attempts = 0;
        while (!window.notificationManager && attempts < 20) {
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }

        if (window.notificationManager) {
            await updateNotificationStatus();
        } else {
            console.warn('NotificationManager failed to initialize');
        }
    });
</script>
@endif
