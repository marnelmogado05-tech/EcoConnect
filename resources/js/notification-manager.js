/**
 * Firebase Cloud Messaging Notification Manager
 * Handles web push notification subscription and token management
 */

class NotificationManager {
    constructor() {
        this.registration = null;
        this.subscription = null;
        // window.VAPID_PUBLIC_KEY = json_encode(env('VAPID_PUBLIC_KEY'));
        this.vapidPublicKey = window.vapidPublicKey || null;
        this.isSupported = 'serviceWorker' in navigator && 'PushManager' in window;

        console.log('NotificationManager initialized');
        console.log('VAPID Key available:', this.vapidPublicKey ? '✓ Yes' : '✗ No');
        console.log('Service Worker support:', this.isSupported ? '✓ Yes' : '✗ No');
    }

    /**
     * Initialize notification system
     */
    async init() {
        if (!this.isSupported) {
            console.warn('Push notifications are not supported in this browser');
            return false;
        }

        try {
            console.log('Initializing NotificationManager...');

            // Register service worker
            if ('serviceWorker' in navigator) {
                this.registration = await navigator.serviceWorker.register('/sw.js', { scope: '/' });
                console.log('✓ Service Worker registered');

                // Check if user is already subscribed
                this.subscription = await this.registration.pushManager.getSubscription();
                if (this.subscription) {
                    console.log('✓ User already subscribed');
                    return true;
                }
            }

            return true;
        } catch (error) {
            console.error('Failed to initialize notifications:', error);
            return false;
        }
    }

    /**
     * Request permission and subscribe to push notifications
     */
    async requestPermissionAndSubscribe() {
        if (!this.isSupported) {
            console.error('Push notifications not supported');
            alert('Push notifications are not supported in your browser');
            return false;
        }

        try {
            console.log('Requesting notification permission...');

            // Request notification permission
            const permission = await Notification.requestPermission();
            console.log('Permission result:', permission);

            if (permission !== 'granted') {
                console.warn('User denied notification permission');
                alert('Please allow notifications in your browser settings to enable push notifications.');
                return false;
            }

            // Subscribe to push notifications
            if (!this.registration) {
                this.registration = await navigator.serviceWorker.ready;
            }

            const subscriptionOptions = {
                userVisibleOnly: true,
            };

            // Add VAPID public key if available
            if (this.vapidPublicKey) {
                console.log('Raw VAPID Key:', this.vapidPublicKey);
                console.log('VAPID Key length:', this.vapidPublicKey.length);
                console.log('VAPID Key type:', typeof this.vapidPublicKey);

                try {
                    const uint8Array = this.urlBase64ToUint8Array(this.vapidPublicKey);
                    console.log('✓ Converted VAPID key to Uint8Array, length:', uint8Array.length);
                    subscriptionOptions.applicationServerKey = uint8Array;
                } catch (conversionError) {
                    console.error('✗ VAPID key conversion failed:', conversionError);
                    throw conversionError;
                }
            } else {
                console.warn('⚠ VAPID key not available - push may fail');
            }

            console.log('Subscription options:', subscriptionOptions);
            const subscription = await this.registration.pushManager.subscribe(subscriptionOptions);
            this.subscription = subscription;
            console.log('✓ Subscribed to push notifications');

            // Send token to server
            const saved = await this.saveTokenToServer(subscription);
            if (!saved) {
                await subscription.unsubscribe();
                console.error('Failed to save token to server');
                return false;
            }

            return true;
        } catch (error) {
            console.error('Error subscribing:', error);
            if (error.name === 'NotAllowedError') {
                alert('Notification permission denied. Please enable notifications in your browser settings.');
            } else {
                alert('Failed to enable notifications: ' + error.message);
            }
            return false;
        }
    }

    /**
     * Convert VAPID key from base64 to Uint8Array
     */
    urlBase64ToUint8Array(base64String) {
        const padding = '='.repeat((4 - base64String.length % 4) % 4);
        const base64 = (base64String + padding)
            .replace(/\-/g, '+')
            .replace(/_/g, '/');

        const rawData = window.atob(base64);
        const outputArray = new Uint8Array(rawData.length);

        for (let i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }
        return outputArray;
    }

    /**
     * Save subscription token to server
     */
    async saveTokenToServer(subscription) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            if (!csrfToken) {
                console.error('CSRF token not found');
                return false;
            }

            const token = JSON.stringify(subscription);
            console.log('Saving token to server...');

            const response = await fetch('/api/notifications/save-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    token: token,
                    device_name: this.getDeviceName(),
                }),
            });

            if (!response.ok) {
                const error = await response.json();
                console.error('Server error:', error);
                return false;
            }

            const data = await response.json();
            if (data.success) {
                console.log('✓ Token saved to server');
                return true;
            } else {
                console.error('Failed to save token:', data.message);
                return false;
            }
        } catch (error) {
            console.error('Error saving token to server:', error);
            return false;
        }
    }

    /**
     * Unsubscribe from push notifications
     */
    async unsubscribe() {
        try {
            if (!this.subscription) {
                this.registration = await navigator.serviceWorker.ready;
                this.subscription = await this.registration.pushManager.getSubscription();
            }

            if (this.subscription) {
                await this.subscription.unsubscribe();
                console.log('✓ Unsubscribed from push notifications');
                await this.removeTokenFromServer(this.subscription);
                this.subscription = null;
                return true;
            }
        } catch (error) {
            console.error('Error unsubscribing:', error);
            return false;
        }
    }

    /**
     * Remove token from server
     */
    async removeTokenFromServer(subscription) {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            await fetch('/api/notifications/remove-token', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken || '',
                },
                body: JSON.stringify({
                    token: JSON.stringify(subscription),
                }),
            });
        } catch (error) {
            console.error('Error removing token from server:', error);
        }
    }

    /**
     * Check notification subscription status
     */
    async getSubscriptionStatus() {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
            const response = await fetch('/api/notifications/status', {
                headers: {
                    'X-CSRF-TOKEN': csrfToken || '',
                },
            });

            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Error getting subscription status:', error);
            return { success: false, enabled: false };
        }
    }

    /**
     * Get device name for identification
     */
    getDeviceName() {
        const userAgent = navigator.userAgent;
        let deviceName = 'Unknown Device';

        if (userAgent.includes('Windows')) {
            deviceName = 'Windows Browser';
        } else if (userAgent.includes('Mac')) {
            deviceName = 'Mac Browser';
        } else if (userAgent.includes('Linux')) {
            deviceName = 'Linux Browser';
        } else if (userAgent.includes('iPhone')) {
            deviceName = 'iPhone';
        } else if (userAgent.includes('iPad')) {
            deviceName = 'iPad';
        } else if (userAgent.includes('Android')) {
            deviceName = 'Android Device';
        }

        if (userAgent.includes('Chrome')) {
            deviceName += ' - Chrome';
        } else if (userAgent.includes('Firefox')) {
            deviceName += ' - Firefox';
        } else if (userAgent.includes('Safari')) {
            deviceName += ' - Safari';
        } else if (userAgent.includes('Edge')) {
            deviceName += ' - Edge';
        }

        return deviceName;
    }
}

// Initialize notification manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.notificationManager = new NotificationManager();
        window.notificationManager.init();
    });
} else {
    window.notificationManager = new NotificationManager();
    window.notificationManager.init();
}
