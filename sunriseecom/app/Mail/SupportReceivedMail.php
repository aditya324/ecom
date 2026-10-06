<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\SupportMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SupportReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public SupportMessage $supportMessage) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New support message from '.$this->supportMessage->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.support-received',
        );
    }

    public static function deliver(SupportMessage $message): bool
    {
        $email = config('mail.support.address') ?: Business::current()->email;

        if (! is_string($email) || $email === '') {
            return false;
        }

        try {
            Mail::to($email)->send(new self($message));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
