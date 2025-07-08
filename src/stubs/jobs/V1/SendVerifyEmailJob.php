<?php

namespace App\Jobs\V1;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\User;
use App\Notifications\VerifyEmail;

class SendVerifyEmailJob implements ShouldQueue
{
    use Queueable;

    protected $user;
    protected $verificationUrl;

    /**
     * Create a new job instance.
     */
    public function __construct(User $user, $verificationUrl)
    {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->user->notify(new VerifyEmail($this->verificationUrl));
    }
}
