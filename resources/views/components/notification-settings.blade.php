<!-- Notification Settings Component -->
<div class="notification-settings card shadow-sm">
    <div class="card-header bg-light">
        <h6 class="mb-0">
            <i class="fas fa-bell me-2 text-success"></i>
            Push Notifications
        </h6>
    </div>
    <div class="card-body">
        <p class="mb-3 text-muted small">
            Enable push notifications to receive real-time updates about your incident reports and other important information.
        </p>

        <div id="notificationStatus" class="mb-3">
            <div class="spinner-border spinner-border-sm text-success" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <span class="ms-2 small">Checking notification status...</span>
        </div>

        <div id="notificationButtons" class="d-none">
            <button type="button" class="btn btn-success w-100 mb-2" id="enableNotificationsBtn" onclick="enableNotifications()">
                <i class="fas fa-bell me-2"></i>Enable Notifications
            </button>
            <button type="button" class="btn btn-outline-danger w-100 d-none" id="disableNotificationsBtn" onclick="disableNotifications()">
                <i class="fas fa-bell-slash me-2"></i>Disable Notifications
            </button>
        </div>

        <div id="notificationNotSupported" class="alert alert-warning d-none mb-0" role="alert">
            <small>
                <i class="fas fa-exclamation-triangle me-2"></i>
                Push notifications are not supported in your browser. Please use a modern browser like Chrome, Firefox, or Edge.
            </small>
        </div>

        <div id="notificationError" class="alert alert-danger d-none mb-0" role="alert">
            <small id="notificationErrorText"></small>
        </div>
    </div>
    <div class="card-footer bg-light border-top-0">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            You can change this setting anytime
        </small>
    </div>
</div>

<script>
    // Check notification support and status on page load
    document.addEventListener('DOMContentLoaded', async function() {
        const statusDiv = document.getElementById('notificationStatus');
        const buttonsDiv = document.getElementById('notificationButtons');
        const notSupportedDiv = document.getElementById('notificationNotSupported');

        // Check if notifications are supported
        if (!window.notificationManager?.isSupported) {
            statusDiv.classList.add('d-none');
            notSupportedDiv.classList.remove('d-none');
            return;
        }

        try {
            // Get current notification status
            const status = await window.notificationManager.getStatus();

            statusDiv.classList.add('d-none');
            buttonsDiv.classList.remove('d-none');

            if (status?.notifications_enabled) {
                // Notifications enabled
                document.getElementById('enableNotificationsBtn').classList.add('d-none');
                document.getElementById('disableNotificationsBtn').classList.remove('d-none');
            } else {
                // Notifications disabled
                document.getElementById('enableNotificationsBtn').classList.remove('d-none');
                document.getElementById('disableNotificationsBtn').classList.add('d-none');
            }
        } catch (error) {
            console.error('Error checking notification status:', error);
            statusDiv.classList.add('d-none');
            buttonsDiv.classList.remove('d-none');
        }
    });

    // Override global enableNotifications to update UI
    const originalEnableNotifications = window.enableNotifications;
    window.enableNotifications = async function() {
        const result = await originalEnableNotifications();
        if (result) {
            document.getElementById('enableNotificationsBtn').classList.add('d-none');
            document.getElementById('disableNotificationsBtn').classList.remove('d-none');
            showNotificationToast('Notifications enabled successfully!', 'success');
        } else {
            showNotificationToast('Failed to enable notifications', 'danger');
        }
    };

    // Override global disableNotifications to update UI
    const originalDisableNotifications = window.disableNotifications;
    window.disableNotifications = async function() {
        const result = await originalDisableNotifications();
        if (result) {
            document.getElementById('enableNotificationsBtn').classList.remove('d-none');
            document.getElementById('disableNotificationsBtn').classList.add('d-none');
            showNotificationToast('Notifications disabled', 'info');
        } else {
            showNotificationToast('Failed to disable notifications', 'danger');
        }
    };

    // Helper function to show toast notification
    function showNotificationToast(message, type = 'info') {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type === 'success' ? 'success' : type === 'danger' ? 'error' : 'info',
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
            });
        } else {
            alert(message);
        }
    }
</script>

<style>
    .notification-settings {
        border-radius: 8px;
        border: 1px solid #e0e0e0;
    }

    .notification-settings .card-header {
        border-bottom: 1px solid #e0e0e0;
        border-radius: 8px 8px 0 0;
    }

    .notification-settings h6 {
        font-weight: 600;
        color: #333;
    }

    .notification-settings .btn {
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .notification-settings .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
