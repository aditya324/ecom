<?php

namespace App\Mail;

use App\Models\Business;
use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class QuoteReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New quote for '.$this->quote->service->name,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.quote-received',
        );
    }

    public static function deliver(Quote $quote): bool
    {
        $email = config('mail.support.address') ?: Business::current()->email;

        if (! is_string($email) || $email === '') {
            return false;
        }

        try {
            $quote->loadMissing('service');
            Mail::to($email)->send(new self($quote));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
