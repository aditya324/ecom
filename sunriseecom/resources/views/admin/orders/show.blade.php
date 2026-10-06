@extends('admin.layout')

@section('title', $order->number)

@section('content')
    <a href="{{ route('admin.orders.index') }}" class="text-sm text-[#6f6a64]">Orders</a>
    <h1 class="mt-2 text-3xl font-semibold tracking-tight text-[#1a1a1a]">{{ $order->number }}</h1>
    <p class="mt-2 text-sm text-[#6f6a64]">{{ $order->name }} · {{ $order->email }}</p>
    @if ($order->razorpay_payment_id)
        <p class="mt-1 text-sm text-[#6f6a64]">Razorpay {{ $order->razorpay_payment_id }}</p>
    @endif
    <p class="mt-1 text-sm text-[#6f6a64]">{{ $order->statusLabel() }}</p>
    @if ((float) $order->refunded_amount > 0)
        <p class="mt-1 text-sm text-[#9b2c2c]">Refunded ₹{{ number_format((float) $order->refunded_amount, 2, '.', ',') }}</p>
    @endif
    @if ($errors->has('order'))
        <p class="mt-4 rounded-xl border border-[#f0c9c4] bg-[#fff4f2] px-4 py-3 text-sm font-medium text-[#9b2c2c]">{{ $errors->first('order') }}</p>
    @endif

    @php
        $parts = $order->paymentParts();
        $subscription = $order->subscriptions->first(fn ($item) => $item->canManage());
    @endphp
    @if (in_array($order->status, ['placed', 'in_progress', 'delivered'], true))
        <form method="POST" action="{{ route('admin.orders.update', $order) }}" class="mt-6 max-w-xl rounded-2xl border border-[#ebe6df] bg-white p-5">
            @csrf
            @method('PUT')
            <label class="block">
                <span class="text-sm font-medium text-[#1a1a1a]">Status</span>
                <select name="status" class="mt-1 h-11 w-full rounded-xl border border-[#ece7e0] px-3 text-sm">
                    @foreach (['placed' => 'Placed', 'in_progress' => 'In progress', 'delivered' => 'Delivered'] as $value => $label)
                        <option value="{{ $value }}" @selected(old('status', $order->status) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label class="mt-3 block">
                <span class="text-sm font-medium text-[#1a1a1a]">Note for the customer</span>
                <textarea name="customer_note" rows="3" maxlength="1000" class="mt-1 w-full rounded-xl border border-[#ece7e0] px-3 py-2 text-sm" placeholder="What happens next">{{ old('customer_note', $order->customer_note) }}</textarea>
                @error('customer_note')<span class="mt-1 block text-xs text-[#9b2c2c]">{{ $message }}</span>@enderror
            </label>
            <button type="submit" class="mt-3 inline-flex h-10 items-center rounded-full bg-[#111111] px-4 text-sm font-medium text-white">Save</button>
        </form>
    @elseif ($order->customer_note)
        <p class="mt-4 max-w-xl rounded-2xl border border-[#ebe6df] bg-white px-5 py-4 text-sm text-[#3a3632]">{{ $order->customer_note }}</p>
    @endif

    @if (in_array($order->status, \App\Models\Order::settledStatuses(), true))
        <form method="POST" action="{{ route('admin.orders.invoice', $order) }}" class="mt-4">
            @csrf
            <button type="submit" class="inline-flex h-10 items-center rounded-full bg-[#111111] px-4 text-sm font-medium text-white">Email invoice</button>
        </form>
    @endif

    <div class="mt-4 flex flex-col gap-3">
        @foreach ($parts as $part)
            @if ($part['refunded'])
                <p class="text-sm font-medium text-[#9b2c2c]">{{ $part['label'] }} refunded · ₹{{ number_format($part['amount'] / 100, 2, '.', ',') }}</p>
            @else
                <form method="POST" action="{{ route('admin.orders.refund', $order) }}" data-confirm-title="Refund {{ $part['label'] }}?" data-confirm-body="Razorpay will return ₹{{ number_format($part['amount'] / 100, 2, '.', ',') }} for {{ $part['label'] }}. {{ count($parts) > 1 ? 'The other payment on this order stays paid.' : '' }}" data-confirm-accept="Refund" data-confirm-tone="danger">
                    @csrf
                    <input type="hidden" name="part" value="{{ $part['key'] }}">
                    <button type="submit" class="inline-flex h-10 items-center rounded-full bg-[#9b2c2c] px-4 text-sm font-medium text-white">Refund {{ $part['label'] }}</button>
                </form>
            @endif
        @endforeach
        @if ($subscription)
            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" data-confirm-title="Cancel this subscription?" data-confirm-body="Razorpay will stop the renewals. Money already charged stays paid until you refund it." data-confirm-accept="Cancel subscription" data-confirm-tone="danger">
                @csrf
                <button type="submit" class="inline-flex h-10 w-fit items-center rounded-full border border-[#9b2c2c] px-4 text-sm font-medium text-[#9b2c2c]">Cancel subscription</button>
            </form>
        @elseif (collect($parts)->contains(fn ($part) => ! $part['refunded']))
            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" data-confirm-title="Cancel this order?" data-confirm-body="The one-time payment will be refunded and the order will be marked refunded." data-confirm-accept="Cancel and refund" data-confirm-tone="danger">
                @csrf
                <button type="submit" class="inline-flex h-10 w-fit items-center rounded-full border border-[#9b2c2c] px-4 text-sm font-medium text-[#9b2c2c]">Cancel order</button>
            </form>
        @endif
    </div>

    <ul class="mt-8 divide-y divide-[#ebe6df] rounded-2xl border border-[#ebe6df] bg-white">
        @foreach ($order->items as $item)
            <li class="flex items-start justify-between gap-4 px-5 py-4">
                <div>
                    <p class="font-semibold text-[#1a1a1a]">{{ $item->service_name }}</p>
                    <p class="mt-1 text-sm text-[#6f6a64]">{{ $item->duration_label }}</p>
                </div>
                <p class="font-semibold text-[#1a1a1a]">{{ $item->money() }}</p>
            </li>
        @endforeach
    </ul>

    @if ((float) $order->discount > 0)
        <p class="mt-4 text-right text-sm text-[#1f9d55]">Discount @if ($order->coupon_code)({{ $order->coupon_code }}) @endif−₹{{ number_format((float) $order->discount, 2, '.', ',') }}</p>
    @endif
    <p class="mt-2 text-right text-sm text-[#6f6a64]">Estimated GST (18%) ₹{{ number_format((float) $order->gst, 2, '.', ',') }}</p>
    <p class="mt-2 text-right text-xl font-semibold text-[#1a1a1a]">{{ $order->money() }}</p>

    @if ($order->subscriptions->isNotEmpty())
        <div class="mt-8 flex flex-col gap-3">
            @foreach ($order->subscriptions as $subscription)
                <div class="rounded-2xl border border-[#ebe6df] bg-white px-5 py-4">
                    <p class="font-semibold text-[#1a1a1a]">{{ $subscription->name }}</p>
                    <p class="mt-1 text-sm text-[#6f6a64]">{{ $subscription->statusLabel() }} · {{ $subscription->period }} · {{ $subscription->paid_count }} of {{ $subscription->total_count }} paid · ₹{{ number_format((float) $subscription->cycle_amount, 2, '.', ',') }}</p>
                    @if ($subscription->razorpay_subscription_id)
                        <p class="mt-1 text-sm text-[#6f6a64]">Subscription {{ $subscription->razorpay_subscription_id }}</p>
                    @endif
                    @if ($subscription->razorpay_payment_id)
                        <p class="mt-1 text-sm text-[#6f6a64]">Payment {{ $subscription->razorpay_payment_id }}</p>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
@endsection
