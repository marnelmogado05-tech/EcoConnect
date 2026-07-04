<?php

namespace App\Jobs;

use App\Services\WebPushNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendPushNotification implements ShouldQueue
{
    use Queueable;

    protected $userId;
    protected $title;
    protected $body;
    protected $data;

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $title, $body, $data = [])
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(WebPushNotificationService $notificationService): void
    {
        Log::info('SendPushNotification job started', [
            'user_id' => $this->userId,
            'title' => $this->title,
        ]);

        try {
            $notificationService->sendToUser(
                $this->userId,
                $this->title,
                $this->body,
                $this->data
            );

            // Flush any pending requests
            $notificationService->flush();

            Log::info('SendPushNotification job completed', ['user_id' => $this->userId]);
        } catch (\Exception $e) {
            Log::error('SendPushNotification job failed', [
                'user_id' => $this->userId,
                'error' => $e->getMessage(),
            ]);

            $this->fail($e);
        }
    }
}
