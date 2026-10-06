<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\InvoiceMail;
use App\Mail\OrderProgressMail;
use App\Mail\RefundMail;
use App\Models\Order;
use App\Support\AdminFilter;
use App\Support\Razorpay;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'status' => AdminFilter::choice($request, 'status', ['placed', 'in_progress', 'delivered', 'refunded', 'cancelled', 'pending', 'all']),
            'kind' => AdminFilter::choice($request, 'kind', ['once', 'subscription']),
            'min' => AdminFilter::number($request, 'min'),
            'max' => AdminFilter::number($request, 'max'),
            'sort' => AdminFilter::choice($request, 'sort', ['newest', 'oldest', 'total_desc', 'total_asc']) ?: 'newest',
        ];

        $orders = Order::query();

        if ($filters['status'] === '') {
            $orders->whereIn('status', Order::settledStatuses());
        } elseif ($filters['status'] !== 'all') {
            $orders->where('status', $filters['status']);
        }

        if ($filters['q'] !== '') {
            $term = AdminFilter::like($filters['q']);
            $orders->where(function ($query) use ($term): void {
                $query->where('number', 'like', $term)
                    ->orWhere('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            });
        }

        if ($filters['kind'] === 'subscription') {
            $orders->where(function ($query): void {
                $query->whereHas('subscriptions')->orWhereNotNull('subscription_id');
            });
        } elseif ($filters['kind'] === 'once') {
            $orders->whereDoesntHave('subscriptions')->whereNull('subscription_id');
        }

        if ($filters['min'] !== null) {
            $orders->where('total', '>=', $filters['min']);
        }

        if ($filters['max'] !== null) {
            $orders->where('total', '<=', $filters['max']);
        }

        match ($filters['sort']) {
            'oldest' => $orders->oldest(),
            'total_desc' => $orders->orderByDesc('total'),
            'total_asc' => $orders->orderBy('total'),
            default => $orders->latest(),
        };

        $orders = $orders->get();

        return view('admin.orders.index', [
            'orders' => $orders,
            'collected' => Order::collected(),
            'shown' => $orders->sum(fn (Order $order) => (float) $order->total - (float) $order->refunded_amount),
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters, ['sort' => 'newest']),
        ]);
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'subscriptions', 'linkedSubscription']);

        return view('admin.orders.show', [
            'order' => $order,
        ]);
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        if (! in_array($order->status, ['placed', 'in_progress', 'delivered'], true)) {
            return back()->withErrors(['order' => 'This order can no longer be updated.']);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['placed', 'in_progress', 'delivered'])],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $note = trim((string) ($data['customer_note'] ?? '')) ?: null;
        $changed = $order->status !== $data['status'] || $order->customer_note !== $note;

        $order->update([
            'status' => $data['status'],
            'customer_note' => $note,
        ]);

        if (! $changed) {
            return back()->with('status', 'Order updated.');
        }

        if (! OrderProgressMail::deliver($order->fresh())) {
            return back()->with('status', 'Order updated.')->withErrors(['order' => 'The update email was not sent.']);
        }

        return back()->with('status', 'Order updated. '.$order->email.' was emailed.');
    }

    public function invoice(Order $order): RedirectResponse
    {
        if (! in_array($order->status, Order::settledStatuses(), true)) {
            return back()->withErrors(['order' => 'An invoice can be emailed after the order is placed.']);
        }

        $order->load('items');

        try {
            Mail::to($order->email)->send(new InvoiceMail($order));
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['order' => 'The invoice email was not sent.']);
        }

        return back()->with('status', 'Invoice emailed to '.$order->email.'.');
    }

    public function refund(Request $request, Order $order, Razorpay $razorpay): RedirectResponse
    {
        $failed = $this->refundPart($request, $order, $razorpay);

        if ($failed instanceof RedirectResponse) {
            return $failed;
        }

        return back()->with('status', 'Payment refunded.');
    }

    public function cancel(Request $request, Order $order, Razorpay $razorpay): RedirectResponse
    {
        $subscriptions = $order->manageableSubscriptions();

        if ($subscriptions->isEmpty()) {
            $request->merge(['part' => 'once']);
            $failed = $this->refundPart($request, $order, $razorpay);

            if ($failed instanceof RedirectResponse) {
                return $failed;
            }

            return back()->with('status', 'Order cancelled and the payment was refunded.');
        }

        try {
            foreach ($subscriptions as $subscription) {
                if ($subscription->razorpay_subscription_id !== null) {
                    $razorpay->cancelSubscription($subscription->razorpay_subscription_id);
                }

                $subscription->update(['status' => 'cancelled']);
            }
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['order' => 'This subscription could not be cancelled.']);
        }

        return back()->with('status', 'Subscription cancelled. The charge already taken is still paid until you refund it.');
    }

    private function refundPart(Request $request, Order $order, Razorpay $razorpay): ?RedirectResponse
    {
        if (! in_array($order->status, Order::settledStatuses(), true)) {
            return back()->withErrors(['order' => 'This order has no payment to refund.']);
        }

        $parts = collect($order->paymentParts())->where('refunded', false)->values();
        $key = $request->string('part')->toString();

        if ($key === '' && $parts->count() === 1) {
            $key = $parts->first()['key'];
        }

        $part = $parts->firstWhere('key', $key);

        if ($part === null || $part['amount'] < 100) {
            return back()->withErrors(['order' => 'This payment is already refunded.']);
        }

        try {
            $razorpay->refundPayment($part['payment_id'], $part['amount']);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors(['order' => 'This payment could not be refunded.']);
        }

        if ($order->recordRefund($part['payment_id'], $part['amount']) && ! RefundMail::deliver($order->fresh())) {
            return back()->with('status', 'Payment refunded.')->withErrors(['order' => 'The refund email was not sent.']);
        }

        return null;
    }
}
