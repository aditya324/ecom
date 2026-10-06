<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Your quote for {{ $quote->service->name }}</title>
</head>
<body style="margin:0;background:#f7f4ef;color:#1a1a1a;font-family:Arial,sans-serif;">
    <div style="max-width:560px;margin:0 auto;padding:32px 20px;">
        <p style="margin:0;font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:#b8860b;">Sunrise</p>
        <h1 style="margin:8px 0 0;font-size:24px;">Your quote for {{ $quote->service->name }}</h1>
        <p style="margin:16px 0 0;font-size:15px;line-height:1.6;">Hello {{ $quote->name }},</p>
        <p style="margin:12px 0 0;font-size:15px;line-height:1.6;">Sunrise replied to your request.</p>
        <p style="margin:20px 0 0;font-size:15px;">Price</p>
        <p style="margin:4px 0 0;font-size:28px;font-weight:700;">{{ $quote->money() }}</p>
        <p style="margin:8px 0 0;font-size:14px;line-height:1.6;color:#6f6a64;">18% GST is added when you pay.</p>
        <p style="margin:20px 0 0;font-size:15px;line-height:1.6;white-space:pre-line;">{{ $quote->reply }}</p>
        @if ($quote->pay_token)
            <p style="margin:24px 0 0;"><a href="{{ route('quotes.pay', $quote->pay_token) }}" style="color:#1a1a1a;">Pay this quote</a></p>
        @endif
    </div>
</body>
</html>
