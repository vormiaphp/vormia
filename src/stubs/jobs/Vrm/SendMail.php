<?php

namespace App\Jobs\Vrm;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Mail;

class SendMail implements ShouldQueue
{
    use Queueable;

    protected $email;
    protected $details;
    protected $subject;
    protected $reply_to;
    protected $cc_recipients;
    protected $bcc_recipients;
    protected $attachments;

    /**
     * Create a new job instance.
     */
    public function __construct(string $email, array $details, string $subject, ?string $replyto = null, array $ccrecipients = [], array $bccrecipients = [], array $attachment = [])
    {
        $this->email = $email;
        $this->details = $details;
        $this->subject = $subject;

        $this->reply_to = $replyto;
        $this->cc_recipients = $ccrecipients;
        $this->bcc_recipients = $bccrecipients;
        $this->attachments = $attachment;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Todo: send email & phone number verification
        Mail::to($this->email)->send(new \App\Mail\SendEmail(
            _details: $this->details,
            _subject: $this->subject,
            _replyToEmail: $this->reply_to,
            _ccRecipients: $this->cc_recipients,
            _bccRecipients: $this->bcc_recipients,
            _attachments: $this->attachments
        ));
    }
}
