<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>New support message from {{ $supportMessage->name }}</title>
</head>
<body style="margin:0;background:#f7f4ef;color:#1a1a1a;font-family:Arial,sans-serif;">
    <div style="max-width:560px;margin:0 auto;padding:32px 20px;">
        <p style="margin:0;font-size:12px;letter-spacing:0.14em;text-transform:uppercase;color:#b8860b;">Sunrise Digital</p>
        <h1 style="margin:8px 0 0;font-size:24px;">New support message</h1>
        <p style="margin:16px 0 0;font-size:15px;line-height:1.6;">{{ $supportMessage->name }} ({{ $supportMessage->email }}) wrote:</p>
        <p style="margin:20px 0 0;font-size:15px;line-height:1.6;white-space:pre-line;">{{ $supportMessage->body }}</p>
        <p style="margin:24px 0 0;"><a href="{{ route('admin.support.show', $supportMessage) }}" style="color:#1a1a1a;">Open this message</a></p>
    </div>
</body>
</html>
