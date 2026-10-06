<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

class OrderProgressMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on order '.$this->order->number,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.order-progress',
        );
    }

    public static function deliver(Order $order): bool
    {
        try {
            Mail::to($order->email)->send(new self($order));

            return true;
        } catch (Throwable $exception) {
            report($exception);

            return false;
        }
    }
}
