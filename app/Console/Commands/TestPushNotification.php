<?php

namespace App\Console\Commands;

use App\Jobs\SendPushNotification;
use App\Models\PushSubscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestPushNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:test {user_id : The ID of the user to send test notification to}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test push notification to a user';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $userId = $this->argument('user_id');

        $this->info('═══════════════════════════════════════════');
        $this->info('    EcoConnect Test Push Notification');
        $this->info('═══════════════════════════════════════════');

        $subscriptionCount = PushSubscription::activeFor((int) $userId)->count();

        if ($subscriptionCount === 0) {
            $this->error("✗ User {$userId} has no active push subscriptions");

            return;
        }

        $this->info("✓ Found {$subscriptionCount} active subscription(s) for user {$userId}");
        $this->newLine();

        // Send test notification
        $this->info('Sending test notification...');

        SendPushNotification::dispatch($userId, 'Test Notification', 'This is a test push notification from EcoConnect', [
            'type' => 'test',
            'timestamp' => now()->toIso8601String(),
        ]);

        $this->info('✓ Test notification queued successfully');
        $this->newLine();
        $this->info('The notification will be sent when the queue is processed.');
        $this->info('To process the queue: php artisan queue:work');
    }
}
