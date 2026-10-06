<?php

namespace App\Mail;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SubscriptionPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Subscription $subscription) {}

    public function envelope(): Envelope
    {
        $subject = $this->subscription->status === 'halted'
            ? 'Renewals stopped for '.$this->subscription->name
            : 'Payment failed for '.$this->subscription->name;

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.subscription-payment',
        );
    }

    public static function deliver(Subscription $subscription): bool
    {
        $subscription->loadMissing(['order', 'user']);
        $email = $subscription->order?->email ?: $subscription->user?->email;

        if (! is_string($email) || $email === '') {
            return false;
        }

        try {
            Mail::to($email)->send(new self($subscription));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
