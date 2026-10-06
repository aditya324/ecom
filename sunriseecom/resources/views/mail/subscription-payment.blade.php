<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $subscription->status === 'halted' ? 'Renewals stopped' : 'Payment failed' }} for {{ $subscription->name }}</title>
</head>
<body style="margin:0;background:#f7f4ef;color:#1a1a1a;font-family:Arial,sans-serif;">
    <div style="max-width:560px;margin:0 auto;padding:32px 20px;">
        <p style="margin:0;font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:#b8860b;">Sunrise Digital</p>
        <h1 style="margin:8px 0 0;font-size:24px;">{{ $subscription->status === 'halted' ? 'Renewals stopped' : 'Payment failed' }}</h1>
        <p style="margin:16px 0 0;font-size:15px;line-height:1.6;">Hello {{ $subscription->order?->name ?? $subscription->user?->name ?? 'there' }},</p>
        @if ($subscription->status === 'halted')
            <p style="margin:8px 0 0;font-size:15px;line-height:1.6;">The renewal for {{ $subscription->name }} kept failing, so Razorpay has stopped further charges. Update the card to start them again.</p>
        @else
            <p style="margin:8px 0 0;font-size:15px;line-height:1.6;">The renewal for {{ $subscription->name }} did not go through. Update the card so the next charge can succeed.</p>
        @endif
        @if ($subscription->short_url)
            <p style="margin:24px 0 0;"><a href="{{ $subscription->short_url }}" style="color:#1a1a1a;">Update card</a></p>
        @endif
        @if ($subscription->order)
            <p style="margin:16px 0 0;"><a href="{{ route('orders.show', $subscription->order) }}" style="color:#1a1a1a;">View order {{ $subscription->order->number }}</a></p>
        @endif
    </div>
</body>
</html>
