<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->number }}</title>
    <style>
        body { margin: 0; color: #1a1a1a; font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { margin: 8px 0 0; font-size: 22px; }
        .muted { color: #6f6a64; }
        .gold { color: #b8860b; font-size: 11px; letter-spacing: 1px; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; }
        th { text-align: left; font-size: 10px; color: #8a8680; text-transform: uppercase; border-bottom: 1px solid #ebe6df; padding: 8px 6px; }
        td { border-bottom: 1px solid #f3eee6; padding: 10px 6px; vertical-align: top; }
        .right { text-align: right; }
        .totals { width: 240px; margin-left: auto; margin-top: 16px; }
        .totals td { border: 0; padding: 4px 0; }
        .total { font-size: 16px; font-weight: bold; border-top: 1px solid #ebe6df; }
        .green { color: #1f9d55; }
        .red { color: #9b2c2c; }
    </style>
</head>
<body>
    <table>
        <tr>
            <td style="border:0;padding:0;">
                @php $business = \App\Models\Business::current(); @endphp
                <div class="gold">{{ $business->legal_name }}</div>
                <h1>Invoice</h1>
                @if ($business->address)
                    <p class="muted" style="margin:8px 0 0;white-space:pre-line;">{{ $business->address }}</p>
                @endif
                @if ($business->gstin)
                    <p style="margin:4px 0 0;">GSTIN {{ $business->gstin }}</p>
                @endif
            </td>
            <td class="right" style="border:0;padding:0;">
                <strong>{{ $order->number }}</strong><br>
                <span class="muted">{{ $order->created_at->format('d M Y') }}</span><br>
                <span class="muted">{{ ucfirst($order->status) }}</span>
            </td>
        </tr>
    </table>

    <p style="margin-top:24px;" class="gold">Billed to</p>
    <p style="margin:4px 0 0;">
        <strong>{{ $order->name }}</strong><br>
        <span class="muted">{{ $order->email }}</span>
        @if ($order->billing_address)
            <br>{{ $order->billing_address }}
            <br>{{ $order->billing_city }}, {{ $order->billing_state }} {{ $order->billing_pin }}
        @endif
        @if ($order->billing_gstin)
            <br>GSTIN {{ $order->billing_gstin }}
        @endif
    </p>

    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th class="right">Price</th>
                <th class="right">GST</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                @php
                    $line = (float) $item->price - (float) $item->discount + (float) $item->gst;
                @endphp
                <tr>
                    <td>
                        <strong>{{ $item->service_name }}</strong><br>
                        <span class="muted">{{ $item->duration_label }}</span>
                        @if ((float) $item->discount > 0)
                            <br><span class="green">Discount −₹{{ number_format((float) $item->discount, 2, '.', ',') }}</span>
                        @endif
                    </td>
                    <td class="right">₹{{ number_format((float) $item->price, 2, '.', ',') }}</td>
                    <td class="right">₹{{ number_format((float) $item->gst, 2, '.', ',') }}</td>
                    <td class="right">₹{{ number_format($line, 2, '.', ',') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        @if ((float) $order->discount > 0)
            <tr>
                <td class="green">Discount @if ($order->coupon_code)({{ $order->coupon_code }}) @endif</td>
                <td class="right green">−₹{{ number_format((float) $order->discount, 2, '.', ',') }}</td>
            </tr>
        @endif
        <tr>
            <td class="muted">GST (18%)</td>
            <td class="right muted">₹{{ number_format((float) $order->gst, 2, '.', ',') }}</td>
        </tr>
        <tr>
            <td class="total">Total</td>
            <td class="right total">₹{{ number_format((float) $order->total, 2, '.', ',') }}</td>
        </tr>
        @if ((float) $order->refunded_amount > 0)
            <tr>
                <td class="red">Refunded</td>
                <td class="right red">₹{{ number_format((float) $order->refunded_amount, 2, '.', ',') }}</td>
            </tr>
        @endif
    </table>

    <p class="muted" style="margin-top:28px;font-size:10px;">GST is 18% added on the price before tax.</p>
</body>
</html>
