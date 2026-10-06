<?php

namespace App\Mail;

use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SupportReplyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupportMessage $supportMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Reply from Sunrise',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.support-reply',
        );
    }
}
