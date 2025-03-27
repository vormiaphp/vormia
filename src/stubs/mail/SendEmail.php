<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    // Mails
    public $subject;
    public $replyToEmail;
    public $ccRecipients;
    public $bccRecipients;

    // Values
    public $logo;
    public $title;
    public $subtitle;
    public $body;
    public $outro;
    public $btn;
    public $btn_extra;

    public $attachments; // New property for attachments

    /**
     * Create a new message instance.
     */
    public function __construct(?array $_details = null, ?string $_subject = null, ?string $_replyToEmail = null, array $_ccRecipients = [], array $_bccRecipients = [], array $_attachments = [])
    {
        // Mail
        $this->subject = $_subject ?? 'NEW MAIL ALERT!';
        $this->replyToEmail = $_replyToEmail;
        $this->ccRecipients = $_ccRecipients;
        $this->bccRecipients = $_bccRecipients;

        // Assign
        $this->logo = $_details['logo'] ? $_details['logo'] : null;
        $this->title = $_details['title'] ? $_details['title'] : null;
        $this->subtitle = $_details['subtitle'] ? $_details['subtitle'] : null;
        $this->body = $_details['body'] ? $_details['body'] : null;
        $this->outro = $_details['outro'] ? $_details['outro'] : null;
        $this->btn = $_details['btn'] ? $_details['btn'] : ['title' => null, 'link' => null];

        $this->btn_extra = $_details['btn_extra'] ? $_details['btn_extra'] : null;

        // Store attachments
        $this->attachments = $_attachments;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->subject,

            replyTo: $this->replyToEmail ? [$this->replyToEmail] : [], // Only include if not null
            cc: array_filter($this->ccRecipients), // Remove empty values
            bcc: array_filter($this->bccRecipients) // Remove empty values
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.general.sendmail',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachmentsList = [];

        foreach ($this->attachments as $attachment) {
            if (isset($attachment['path'])) {
                $attachmentItem = \Illuminate\Mail\Mailables\Attachment::fromPath($attachment['path']);

                // Add optional properties if available
                if (isset($attachment['name'])) {
                    $attachmentItem->as($attachment['name']);
                }

                if (isset($attachment['mime'])) {
                    $attachmentItem->withMime($attachment['mime']);
                }

                $attachmentsList[] = $attachmentItem;
            }
        }

        return $attachmentsList;
    }
}
