<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Refund {{ $order->number }}</title>
</head>
<body style="margin:0;background:#f7f4ef;color:#1a1a1a;font-family:Arial,sans-serif;">
    <div style="max-width:560px;margin:0 auto;padding:32px 20px;">
        <p style="margin:0;font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:#b8860b;">Sunrise Digital</p>
        <h1 style="margin:8px 0 0;font-size:24px;">Refund {{ $order->number }}</h1>
        <p style="margin:16px 0 0;font-size:15px;line-height:1.6;">Hello {{ $order->name }},</p>
        <p style="margin:8px 0 0;font-size:15px;line-height:1.6;">A refund has been sent back through Razorpay for this order. It can take a few days to reach the original payment method.</p>
        <p style="margin:20px 0 0;font-size:15px;">Refunded</p>
        <p style="margin:4px 0 0;font-size:28px;font-weight:700;">₹{{ number_format((float) $order->refunded_amount, 2, '.', ',') }}</p>
        <p style="margin:24px 0 0;"><a href="{{ route('orders.show', $order) }}" style="color:#1a1a1a;">View order {{ $order->number }}</a></p>
    </div>
</body>
</html>
