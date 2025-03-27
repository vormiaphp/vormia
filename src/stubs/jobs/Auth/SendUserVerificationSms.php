<?php

namespace App\Jobs\Auth;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendUserVerificationSms implements ShouldQueue
{
    use Queueable;

    protected $phoneNumber;
    protected $message;

    /**
     * Create a new job instance.
     */
    public function __construct(string $phoneNumber, string $message)
    {
        $this->phoneNumber = $phoneNumber;
        $this->message = $message;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Message
        \App\Services\AfricasTalkingService::send_message(
            $this->phoneNumber,
            $this->message
        );
    }
}
