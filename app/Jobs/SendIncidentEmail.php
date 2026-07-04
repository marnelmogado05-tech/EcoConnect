<?php

namespace App\Jobs;

use App\Mail\IncidentReportedEmail;
use App\Models\Incident;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendIncidentEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $incident;
    public $user;

    public function __construct(Incident $incident, User $user)
    {
        $this->incident = $incident;
        $this->user = $user;
    }

    public function handle()
    {
        Mail::to($this->user->email)
            ->send(new IncidentReportedEmail($this->incident, $this->user));
    }
}
