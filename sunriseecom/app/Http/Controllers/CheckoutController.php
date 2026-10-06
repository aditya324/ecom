<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\PaymentRequest;
use App\Mail\InvoiceMail;
use App\Mail\RefundMail;
use App\Mail\SubscriptionPaymentMail;
use App\Models\CartDrop;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\CouponUse;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Quote;
use App\Models\Service;
use App\Models\Subscription;
use App\Support\Bill;
use App\Support\Cart;
use App\Support\Razorpay;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Throwable;

class CheckoutController extends Controller
{
    public function buy(CartItemRequest $request, Service $service): RedirectResponse
    {
        abort_unless($service->is_active, 404);

        $months = $request->filled('months') ? $request->integer('months') : null;

        if ($service->offer($months) === null) {
            return back()->withErrors(['months' => 'Choose a plan.']);
        }

        $request->session()->put('checkout', [
            'source' => 'buy',
            'lines' => [
                $service->id.'-'.($months ?? 0) => [
                    'service_id' => $service->id,
                    'months' => $months,
                ],
            ],
        ]);

        if ($request->user() === null) {
            $request->session()->put('url.intended', route('checkout.create'));

            return redirect()->route('login');
        }

        return redirect()->route('checkout.create');
    }

    public function create(Request $request): View|RedirectResponse
    {
        if (($request->session()->get('checkout.source')) === 'plan') {
            return $this->planCreate($request);
        }

        if (($request->session()->get('checkout.source')) === 'quote') {
            return $this->quoteCreate($request);
        }

        $items = $this->items($request);

        if ($items === []) {
            return redirect()->route('cart.index');
        }

        $pricing = $this->pricing($request, $items);

        return view('checkout.create', [
            'items' => $items,
            'bill' => $pricing,
            'hasSubscription' => $this->hasSubscription($items),
        ]);
    }

    public function store(CheckoutRequest $request, Razorpay $razorpay): JsonResponse|RedirectResponse
    {
        if (($request->session()->get('checkout.source')) === 'plan') {
            return $this->storePlan($request, $razorpay);
        }

        if (($request->session()->get('checkout.source')) === 'quote') {
            return $this->storeQuote($request, $razorpay);
        }

        $items = $this->items($request);

        if ($items === []) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Your cart is empty.'], 422);
            }

            return redirect()->route('cart.index');
        }

        $coupon = Coupon::applied($request);

        $reason = $coupon?->rejection($request->user()->id, $request->string('email')->toString());

        if ($coupon !== null && $coupon->discountFor($items) > 0 && $reason !== null) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $reason,
                    'errors' => ['email' => [$reason]],
                ], 422);
            }

            return back()
                ->withInput()
                ->withErrors(['email' => $reason]);
        }

        $pricing = $this->pricing($request, $items);

        if ($pricing['total'] < 1) {
            return response()->json(['message' => 'This order cannot be paid online.'], 422);
        }

        $created = [];
        $steps = [];
        $couponClaimed = false;
        $kept = [];
        $order = null;

        try {
            foreach ($this->groups($items) as $indexes) {
                $existing = $this->matchingPending($request, $items, $pricing, $indexes, $kept);

                if ($existing !== null) {
                    $kept[] = $existing->id;
                    $steps[] = $this->stepFrom($existing);
                    $order = $existing;

                    if ($existing->coupon_code !== null) {
                        $couponClaimed = true;
                    }

                    continue;
                }

                $order = $this->makeOrder($request, $items, $pricing, $indexes, $couponClaimed);
                $created[] = $order;
                $kept[] = $order->id;
                $steps = array_merge($steps, $this->steps(
                    $razorpay,
                    $order,
                    array_map(fn (int $index) => $items[$index], $indexes),
                    array_map(fn (int $index) => $pricing['lines'][$index], $indexes),
                ));
            }

            $this->retireUnpaid($request, $razorpay, $kept);
        } catch (Throwable $exception) {
            foreach ($created as $createdOrder) {
                $createdOrder->delete();
            }

            report($exception);

            return response()->json(['message' => $this->paymentError($exception)], 502);
        }

        if (! $order instanceof Order) {
            return response()->json(['message' => 'This order cannot be paid online.'], 422);
        }

        return $this->payload($razorpay, $order, $steps);
    }

    public function payment(PaymentRequest $request, Razorpay $razorpay): JsonResponse|RedirectResponse
    {
        $orders = $this->confirm($request, $razorpay);

        if (! $orders instanceof Collection) {
            return $orders;
        }

        $placed = null;

        foreach ($orders as $order) {
            if (! $this->ready($order)) {
                continue;
            }

            $this->place($order);
            $order->refresh();

            if ($order->status !== 'placed') {
                continue;
            }

            $this->releasePaidLines($request, $order);
            $placed = $order;
        }

        if ($request->expectsJson()) {
            return response()->json(['done' => false]);
        }

        if ($placed === null) {
            return redirect()->route('checkout.create');
        }

        $this->releaseCheckout($request);

        return redirect()
            ->route('orders.show', $placed)
            ->with('status', 'Order placed.');
    }

    public function webhook(Request $request, Razorpay $razorpay): Response
    {
        $signature = (string) $request->header('X-Razorpay-Signature', '');

        if (! $razorpay->webhookValid($request->getContent(), $signature)) {
            abort(400);
        }

        $payload = $request->json()->all();
        $event = $payload['event'] ?? null;

        if ($event === 'payment.captured') {
            $this->paymentCaptured($payload, $razorpay);
        } elseif ($event === 'subscription.charged') {
            $this->subscriptionCharged($payload);
        } elseif (in_array($event, ['subscription.pending', 'subscription.halted', 'subscription.cancelled', 'subscription.completed'], true)) {
            $this->subscriptionStatus($payload, $event);
        } elseif ($event === 'refund.processed') {
            $this->refundProcessed($payload);
        }

        return response()->noContent();
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function paymentCaptured(array $payload, Razorpay $razorpay): void
    {
        $payment = $payload['payload']['payment']['entity'] ?? null;

        if (! is_array($payment) || ! is_string($payment['id'] ?? null) || ! is_string($payment['order_id'] ?? null)) {
            return;
        }

        $order = Order::query()->where('razorpay_order_id', $payment['order_id'])->first();

        if ($order === null || ($payment['status'] ?? null) !== 'captured' || ($payment['currency'] ?? null) !== 'INR') {
            return;
        }

        $expected = $order->razorpay_amount ?? $razorpay->paise($order);

        if ((int) $payment['amount'] !== $expected) {
            return;
        }

        if ($order->razorpay_payment_id === null) {
            $order->update(['razorpay_payment_id' => $payment['id']]);
        }

        if (! $this->ready($order)) {
            return;
        }

        $pending = $order->status === 'pending';
        $this->place($order);
        $order->refresh();

        if ($pending && $order->status === 'placed') {
            $this->forgetCartLines($order);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function subscriptionCharged(array $payload): void
    {
        $entity = $payload['payload']['subscription']['entity'] ?? null;
        $payment = $payload['payload']['payment']['entity'] ?? null;

        if (! is_array($entity) || ! is_array($payment) || ! is_string($entity['id'] ?? null) || ! is_string($payment['id'] ?? null)) {
            return;
        }

        if (($payment['status'] ?? null) !== 'captured' || ($payment['currency'] ?? null) !== 'INR') {
            return;
        }

        $subscription = Subscription::query()->where('razorpay_subscription_id', $entity['id'])->first();

        if ($subscription === null || Order::query()->where('razorpay_payment_id', $payment['id'])->exists()) {
            return;
        }

        $shortUrl = is_string($entity['short_url'] ?? null) && $entity['short_url'] !== '' ? $entity['short_url'] : $subscription->short_url;
        $amount = (int) $payment['amount'];

        if ($amount !== (int) round((float) $subscription->cycle_amount * 100)) {
            return;
        }

        $paidCount = max(1, (int) ($entity['paid_count'] ?? 0));
        $firstCharge = $subscription->razorpay_payment_id === null && $subscription->status !== 'cancelled';

        if ($firstCharge) {
            $subscription->update([
                'status' => 'active',
                'razorpay_payment_id' => $payment['id'],
                'paid_count' => $paidCount,
                'short_url' => $shortUrl,
            ]);

            $order = $subscription->order;

            if ($order !== null && $order->razorpay_payment_id === null) {
                $order->update(['razorpay_payment_id' => $payment['id']]);
            }

            if ($order !== null && $order->status === 'pending' && $this->ready($order)) {
                $this->place($order);
                $order->refresh();

                if ($order->status === 'placed') {
                    $this->forgetCartLines($order);
                }
            }

            return;
        }

        $subscription->update([
            'status' => 'active',
            'short_url' => $shortUrl,
            'paid_count' => $paidCount,
        ]);
        $this->recordRenewal($subscription->fresh(), $payment['id']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function subscriptionStatus(array $payload, string $event): void
    {
        $entity = $payload['payload']['subscription']['entity'] ?? null;

        if (! is_array($entity) || ! is_string($entity['id'] ?? null)) {
            return;
        }

        $subscription = Subscription::query()->where('razorpay_subscription_id', $entity['id'])->first();

        if ($subscription === null || $subscription->status === 'cancelled') {
            return;
        }

        $status = match ($event) {
            'subscription.pending' => 'past_due',
            'subscription.halted' => 'halted',
            'subscription.cancelled' => 'cancelled',
            'subscription.completed' => 'completed',
            default => null,
        };

        if ($status === null) {
            return;
        }

        $shortUrl = is_string($entity['short_url'] ?? null) && $entity['short_url'] !== '' ? $entity['short_url'] : $subscription->short_url;
        $shouldMail = in_array($status, ['past_due', 'halted'], true) && $subscription->status !== $status;

        $subscription->update([
            'status' => $status,
            'short_url' => $shortUrl,
        ]);

        if ($shouldMail) {
            SubscriptionPaymentMail::deliver($subscription);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function refundProcessed(array $payload): void
    {
        $payment = $payload['payload']['payment']['entity'] ?? null;
        $refund = $payload['payload']['refund']['entity'] ?? null;
        $paymentId = is_array($payment) ? ($payment['id'] ?? null) : null;

        if (! is_string($paymentId) || $paymentId === '') {
            $paymentId = is_array($refund) ? ($refund['payment_id'] ?? null) : null;
        }

        if (! is_string($paymentId) || $paymentId === '') {
            return;
        }

        $order = Order::query()->where('razorpay_payment_id', $paymentId)->first()
            ?? Subscription::query()->where('razorpay_payment_id', $paymentId)->first()?->order;

        if ($order === null || in_array($paymentId, $order->refunded_payments ?? [], true)) {
            return;
        }

        $refundedPaise = is_array($payment) ? (int) ($payment['amount_refunded'] ?? 0) : 0;

        if ($refundedPaise === 0 && is_array($refund)) {
            $refundedPaise = (int) ($refund['amount'] ?? 0);
        }

        if ($refundedPaise < 100) {
            return;
        }

        if ($order->recordRefund($paymentId, $refundedPaise)) {
            RefundMail::deliver($order->fresh());
        }
    }

    private function recordRenewal(Subscription $subscription, string $paymentId): void
    {
        $source = $subscription->order;
        $item = $source?->items()->first();

        if ($source === null || $item === null) {
            return;
        }

        $label = str_contains($item->duration_label, 'renewal')
            ? $item->duration_label
            : $item->duration_label.' · renewal';

        $order = Order::query()->create([
            'number' => $this->number(),
            'user_id' => $subscription->user_id,
            'subscription_id' => $subscription->id,
            'name' => $source->name,
            'email' => $source->email,
            'billing_address' => $source->billing_address,
            'billing_city' => $source->billing_city,
            'billing_state' => $source->billing_state,
            'billing_pin' => $source->billing_pin,
            'billing_gstin' => $source->billing_gstin,
            'status' => 'placed',
            'razorpay_payment_id' => $paymentId,
            'razorpay_amount' => (int) round((float) $subscription->cycle_amount * 100),
            'discount' => $item->discount,
            'gst' => $item->gst,
            'total' => $subscription->cycle_amount,
        ]);

        $order->items()->create([
            'service_id' => $item->service_id,
            'service_name' => $item->service_name,
            'duration_label' => $label,
            'months' => $item->months,
            'price' => $item->price,
            'discount' => $item->discount,
            'gst' => $item->gst,
        ]);

        $this->emailInvoice($order);
    }

    private function forgetCartLines(Order $order): void
    {
        if ($order->user_id === null || $order->quote_id !== null) {
            return;
        }

        foreach ($order->items as $item) {
            if ($item->service_id === null) {
                continue;
            }

            CartItem::query()
                ->where('user_id', $order->user_id)
                ->where('service_id', $item->service_id)
                ->when(
                    $item->months === null,
                    fn ($query) => $query->whereNull('months'),
                    fn ($query) => $query->where('months', $item->months),
                )
                ->delete();

            CartDrop::query()->create([
                'user_id' => $order->user_id,
                'cart_key' => $item->service_id.'-'.($item->months ?? 0),
            ]);
        }
    }

    /**
     * @param  list<array{key?: string, service: Service, price: float, quantity: int}>  $items
     * @return array{lines: list<array{key: string, gross: int, discount: int, payable: int, gst: int, taxable: int}>, subtotal: int, discount: int, gst: int, taxable: int, total: int, code: ?string}
     */
    private function pricing(Request $request, array $items): array
    {
        return Bill::quote($items, Coupon::applied($request));
    }

    /**
     * @return list<array{key: string, service: Service, months: ?int, label: string, price: float, compare: ?float, quantity: int}>
     */
    private function items(Request $request): array
    {
        $pending = $request->session()->get('checkout');
        $lines = ($pending['source'] ?? null) === 'buy'
            ? $pending['lines']
            : (new Cart($request))->lines();

        return $this->chargeable((new Cart($request))->resolve($lines));
    }

    /**
     * @param  list<array{key: string, service: Service, months: ?int, label: string, price: float, compare: ?float, quantity: int}>  $items
     * @return list<array{key: string, service: Service, months: ?int, label: string, price: float, compare: ?float, quantity: int, cycles?: int}>
     */
    private function chargeable(array $items): array
    {
        return array_map(function (array $item): array {
            if ($item['service']->billing_type !== 'monthly') {
                return $item;
            }

            $months = max(1, (int) ($item['months'] ?? 1));

            if (($item['months'] ?? null) !== null && (int) $item['months'] > 1) {
                $item['price'] = $item['price'] / (int) $item['months'];
            }

            $item['cycles'] = ($item['months'] ?? null) === null ? 12 : $months;
            $item['label'] .= ' · renews monthly';

            return $item;
        }, $items);
    }

    /**
     * One-time services share an order. Each monthly service is its own order,
     * so one paid subscription can finish while another is still unpaid.
     *
     * @param  list<array{service: Service}>  $items
     * @return list<list<int>>
     */
    private function groups(array $items): array
    {
        $once = [];
        $groups = [];

        foreach ($items as $index => $item) {
            if ($item['service']->billing_type === 'monthly') {
                $groups[] = [$index];
            } else {
                $once[] = $index;
            }
        }

        if ($once !== []) {
            array_unshift($groups, $once);
        }

        return $groups;
    }

    /**
     * @param  list<array{service: Service, months: ?int, label: string, quantity: int}>  $items
     * @param  array{lines: list<array{gross: int, discount: int, payable: int, gst: int}>, code: ?string}  $pricing
     * @param  list<int>  $indexes
     */
    private function makeOrder(Request $request, array $items, array $pricing, array $indexes, bool &$couponClaimed): Order
    {
        $discount = 0;
        $gst = 0;
        $total = 0;

        foreach ($indexes as $index) {
            $discount += $pricing['lines'][$index]['discount'];
            $gst += $pricing['lines'][$index]['gst'];
            $total += $pricing['lines'][$index]['payable'];
        }

        $code = null;

        if ($discount > 0 && ! $couponClaimed && $pricing['code'] !== null) {
            $code = $pricing['code'];
            $couponClaimed = true;
        }

        $order = Order::query()->create([
            'number' => $this->number(),
            'user_id' => $request->user()->id,
            ...$this->customerFrom($request),
            'status' => 'pending',
            'coupon_code' => $code,
            'discount' => $discount,
            'gst' => $gst,
            'total' => $total,
        ]);

        $order->items()->createMany(array_map(function (int $index) use ($items, $pricing) {
            $item = $items[$index];

            return [
                'service_id' => $item['service']->id,
                'service_name' => $item['service']->name,
                'duration_label' => $item['quantity'] > 1 ? $item['label'].' × '.$item['quantity'] : $item['label'],
                'months' => $item['months'],
                'price' => $pricing['lines'][$index]['gross'],
                'discount' => $pricing['lines'][$index]['discount'],
                'gst' => $pricing['lines'][$index]['gst'],
            ];
        }, $indexes));

        return $order;
    }

    /**
     * @param  list<array{service: Service, cycles?: int, months?: ?int}>  $items
     * @param  list<array{payable: int}>  $lines
     * @return list<array{type: string, order_id?: string, subscription_id?: string, amount?: int, currency?: string, description: string}>
     */
    private function steps(Razorpay $razorpay, Order $order, array $items, array $lines): array
    {
        $steps = [];
        $once = 0;

        foreach ($lines as $index => $line) {
            if ($items[$index]['service']->billing_type === 'monthly') {
                continue;
            }

            $once += $line['payable'];
        }

        if ($once > 0) {
            $amount = $once * 100;
            $razorpayOrderId = $razorpay->createOrder($order, $amount);
            $order->update([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_amount' => $amount,
            ]);
            $steps[] = [
                'type' => 'order',
                'order_id' => $razorpayOrderId,
                'amount' => $amount,
                'currency' => 'INR',
                'description' => 'Order '.$order->number,
            ];
        }

        foreach ($lines as $index => $line) {
            $item = $items[$index];

            if ($item['service']->billing_type !== 'monthly') {
                continue;
            }

            $steps = array_merge($steps, $this->subscribe(
                $razorpay,
                $order,
                $item['service']->name,
                'monthly',
                $line['payable'] * 100,
                (int) ($item['cycles'] ?? 1),
            ));
        }

        return $steps;
    }

    /**
     * @return list<array{type: string, subscription_id: string, description: string}>
     */
    private function subscribe(Razorpay $razorpay, Order $order, string $name, string $period, int $amount, int $cycles): array
    {
        $planId = $razorpay->createPlan($name, $period, $amount);
        $started = $razorpay->createSubscription($planId, $cycles);

        $order->subscriptions()->create([
            'user_id' => $order->user_id,
            'name' => $name,
            'period' => $period,
            'total_count' => max(1, $cycles),
            'cycle_amount' => $amount / 100,
            'status' => 'pending',
            'razorpay_plan_id' => $planId,
            'razorpay_subscription_id' => $started['id'],
            'short_url' => $started['short_url'],
        ]);

        return [[
            'type' => 'subscription',
            'subscription_id' => $started['id'],
            'description' => $name,
        ]];
    }

    /**
     * @param  list<array{type: string, order_id?: string, subscription_id?: string, amount?: int, currency?: string, description: string}>  $steps
     */
    private function payload(Razorpay $razorpay, Order $order, array $steps): JsonResponse
    {
        return response()->json([
            'key' => $razorpay->key(),
            'name' => config('app.name', 'Sunrise'),
            'prefill' => [
                'name' => $order->name,
                'email' => $order->email,
            ],
            'steps' => $steps,
        ]);
    }

    private function planCreate(Request $request): View|RedirectResponse
    {
        $plan = $this->selectedPlan($request);

        if ($plan === null) {
            $request->session()->forget('checkout');

            return redirect()->route('home');
        }

        $interval = $this->planInterval($request);
        $taxable = $this->planAmount($plan, $interval);

        return view('checkout.create', [
            'items' => [[
                'service' => new Service([
                    'name' => $plan->name,
                    'billing_type' => 'monthly',
                ]),
                'label' => $interval === 'yearly' ? 'Yearly · renews each year' : 'Monthly · renews each month',
                'price' => $taxable,
            ]],
            'bill' => [
                'discount' => 0,
                'code' => null,
                'gst' => Bill::gstOn($taxable),
                'total' => $taxable + Bill::gstOn($taxable),
            ],
            'hasSubscription' => true,
        ]);
    }

    private function storePlan(CheckoutRequest $request, Razorpay $razorpay): JsonResponse
    {
        $plan = $this->selectedPlan($request);

        if ($plan === null) {
            return response()->json(['message' => 'Choose a plan.'], 422);
        }

        $interval = $this->planInterval($request);
        $taxable = $this->planAmount($plan, $interval);
        $gst = Bill::gstOn($taxable);
        $cycles = $interval === 'yearly' ? 5 : 12;

        if ($taxable + $gst < 1) {
            return response()->json(['message' => 'This order cannot be paid online.'], 422);
        }

        $existing = $this->findPending($request->user()->id, $this->signature([[
            'service_id' => null,
            'service_name' => $plan->name,
            'months' => $cycles,
            'price' => $taxable,
            'discount' => 0,
            'gst' => $gst,
        ]]), []);

        if ($existing !== null) {
            $existing->update($this->customerFrom($request));
            $this->retireUnpaid($request, $razorpay, [$existing->id]);

            return $this->payload($razorpay, $existing, [$this->stepFrom($existing)]);
        }

        $order = Order::query()->create([
            'number' => $this->number(),
            'user_id' => $request->user()->id,
            ...$this->customerFrom($request),
            'status' => 'pending',
            'discount' => 0,
            'gst' => $gst,
            'total' => $taxable + $gst,
        ]);

        $order->items()->create([
            'service_id' => null,
            'plan_id' => $plan->id,
            'service_name' => $plan->name,
            'duration_label' => $interval === 'yearly' ? 'Yearly · renews each year' : 'Monthly · renews each month',
            'months' => $cycles,
            'price' => $taxable,
            'discount' => 0,
            'gst' => $gst,
        ]);

        try {
            $steps = $this->subscribe($razorpay, $order, $plan->name, $interval, ($taxable + $gst) * 100, $cycles);
        } catch (Throwable $exception) {
            $order->delete();
            report($exception);

            return response()->json(['message' => $this->paymentError($exception)], 502);
        }

        $this->retireUnpaid($request, $razorpay, [$order->id]);

        return $this->payload($razorpay, $order, $steps);
    }

    private function quoteCreate(Request $request): View|RedirectResponse
    {
        $quote = $this->selectedQuote($request);

        if ($quote === null) {
            $request->session()->forget('checkout');

            return redirect()->route('home');
        }

        $paid = $quote->paidOrder();

        if ($paid !== null) {
            $request->session()->forget('checkout');

            if ($paid->user_id === $request->user()->id) {
                return redirect()->route('orders.show', $paid);
            }

            return redirect()->route('home');
        }

        $taxable = (int) round((float) $quote->quoted_price);

        return view('checkout.create', [
            'items' => [[
                'service' => $quote->service,
                'label' => 'Custom quote',
                'price' => $taxable,
            ]],
            'bill' => [
                'discount' => 0,
                'code' => null,
                'gst' => Bill::gstOn($taxable),
                'total' => $taxable + Bill::gstOn($taxable),
            ],
            'hasSubscription' => false,
        ]);
    }

    private function storeQuote(CheckoutRequest $request, Razorpay $razorpay): JsonResponse
    {
        $quote = $this->selectedQuote($request);

        if ($quote === null) {
            return response()->json(['message' => 'This quote is no longer available.'], 422);
        }

        if ($quote->paidOrder() !== null) {
            return response()->json(['message' => 'This quote is already paid.'], 422);
        }

        $taxable = (int) round((float) $quote->quoted_price);
        $gst = Bill::gstOn($taxable);

        if ($taxable + $gst < 1) {
            return response()->json(['message' => 'This order cannot be paid online.'], 422);
        }

        $existing = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('quote_id', $quote->id)
            ->where('status', 'pending')
            ->whereNull('razorpay_payment_id')
            ->whereNotNull('razorpay_order_id')
            ->first();

        if ($existing !== null && (int) round((float) $existing->total) === $taxable + $gst) {
            $existing->update($this->customerFrom($request));
            $this->retireUnpaid($request, $razorpay, [$existing->id]);

            return $this->payload($razorpay, $existing, [$this->stepFrom($existing)]);
        }

        $order = Order::query()->create([
            'number' => $this->number(),
            'user_id' => $request->user()->id,
            'quote_id' => $quote->id,
            ...$this->customerFrom($request),
            'status' => 'pending',
            'discount' => 0,
            'gst' => $gst,
            'total' => $taxable + $gst,
        ]);

        $order->items()->create([
            'service_id' => $quote->service_id,
            'service_name' => $quote->service->name,
            'duration_label' => 'Custom quote',
            'months' => null,
            'price' => $taxable,
            'discount' => 0,
            'gst' => $gst,
        ]);

        try {
            $amount = ($taxable + $gst) * 100;
            $razorpayOrderId = $razorpay->createOrder($order, $amount);
            $order->update([
                'razorpay_order_id' => $razorpayOrderId,
                'razorpay_amount' => $amount,
            ]);
        } catch (Throwable $exception) {
            $order->delete();
            report($exception);

            return response()->json(['message' => $this->paymentError($exception)], 502);
        }

        $this->retireUnpaid($request, $razorpay, [$order->id]);

        return $this->payload($razorpay, $order->fresh(), [$this->stepFrom($order->fresh())]);
    }

    private function selectedQuote(Request $request): ?Quote
    {
        $id = $request->session()->get('checkout.quote_id');

        if (! is_numeric($id)) {
            return null;
        }

        return Quote::query()
            ->with('service')
            ->whereKey($id)
            ->where('status', 'replied')
            ->whereNotNull('quoted_price')
            ->whereNotNull('pay_token')
            ->whereHas('service')
            ->first();
    }

    private function selectedPlan(Request $request): ?Plan
    {
        $id = $request->session()->get('checkout.plan_id');

        if (! is_numeric($id)) {
            return null;
        }

        return Plan::query()->whereKey($id)->where('is_active', true)->first();
    }

    private function planInterval(Request $request): string
    {
        return $request->session()->get('checkout.interval') === 'monthly' ? 'monthly' : 'yearly';
    }

    private function planAmount(Plan $plan, string $interval): int
    {
        $amount = $interval === 'yearly' ? $plan->yearly_price : $plan->monthly_price;

        return (int) round((float) $amount);
    }

    /**
     * @param  list<array{service: Service}>  $items
     */
    private function hasSubscription(array $items): bool
    {
        foreach ($items as $item) {
            if ($item['service']->billing_type === 'monthly') {
                return true;
            }
        }

        return false;
    }

    private function confirm(PaymentRequest $request, Razorpay $razorpay): Collection|JsonResponse|RedirectResponse
    {
        $payments = $request->input('subscriptions', []);

        if ($request->filled('razorpay_subscription_id')) {
            $payments[] = [
                'razorpay_subscription_id' => $request->string('razorpay_subscription_id')->toString(),
                'razorpay_payment_id' => $request->string('razorpay_payment_id')->toString(),
                'razorpay_signature' => $request->string('razorpay_signature')->toString(),
            ];
        }

        $orders = collect();

        if ($request->filled('razorpay_order_id')) {
            $order = Order::query()
                ->where('user_id', $request->user()->id)
                ->where('razorpay_order_id', $request->string('razorpay_order_id')->toString())
                ->firstOrFail();

            $paymentId = $request->string('razorpay_payment_id')->toString();

            if ($order->razorpay_payment_id === null && ! $razorpay->signatureValid(
                (string) $order->razorpay_order_id,
                $paymentId,
                $request->string('razorpay_signature')->toString(),
            )) {
                return $this->unconfirmed($request);
            }

            if ($order->razorpay_payment_id === null) {
                $order->update(['razorpay_payment_id' => $paymentId]);
            }

            $orders->push($order);
        }

        foreach ($payments as $payment) {
            if (! is_array($payment)) {
                return $this->unconfirmed($request);
            }

            $subscription = Subscription::query()
                ->where('user_id', $request->user()->id)
                ->where('razorpay_subscription_id', $payment['razorpay_subscription_id'] ?? null)
                ->firstOrFail();

            $order = $subscription->order;

            if ($order === null || $order->user_id !== $request->user()->id) {
                return $this->unconfirmed($request);
            }

            $paymentId = (string) ($payment['razorpay_payment_id'] ?? '');

            if ($subscription->status !== 'active' && ! $razorpay->subscriptionSignatureValid(
                $paymentId,
                (string) $subscription->razorpay_subscription_id,
                (string) ($payment['razorpay_signature'] ?? ''),
            )) {
                return $this->unconfirmed($request);
            }

            if ($subscription->status !== 'active') {
                $subscription->update([
                    'status' => 'active',
                    'razorpay_payment_id' => $paymentId,
                ]);
            }

            $orders->push($order);
        }

        if ($orders->isEmpty()) {
            abort(404);
        }

        return $orders->unique('id')->values();
    }

    private function ready(Order $order): bool
    {
        $order->refresh();

        if ($order->razorpay_order_id !== null && $order->razorpay_payment_id === null) {
            return false;
        }

        return $order->subscriptions()->where('status', '!=', 'active')->doesntExist();
    }

    private function place(Order $order): void
    {
        $becamePlaced = false;

        DB::transaction(function () use ($order, &$becamePlaced): void {
            $paymentId = $order->razorpay_payment_id
                ?? $order->subscriptions()->whereNotNull('razorpay_payment_id')->value('razorpay_payment_id');

            $saved = Order::query()
                ->whereKey($order->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'placed',
                    'razorpay_payment_id' => $paymentId,
                ]);

            if ($saved !== 1) {
                return;
            }

            $becamePlaced = true;
            $order->refresh();

            if ($order->coupon_code === null || (float) $order->discount <= 0) {
                return;
            }

            $coupon = Coupon::query()->where('code', $order->coupon_code)->first();

            if ($coupon === null || CouponUse::query()->where('order_id', $order->id)->exists()) {
                return;
            }

            CouponUse::query()->create([
                'coupon_id' => $coupon->id,
                'user_id' => $order->user_id,
                'email' => strtolower($order->email),
                'order_id' => $order->id,
            ]);
        });

        if ($becamePlaced) {
            $this->emailInvoice($order->fresh());
        }
    }

    private function emailInvoice(Order $order): void
    {
        try {
            $order->loadMissing('items');
            Mail::to($order->email)->send(new InvoiceMail($order));
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    /**
     * @return array{name: string, email: string, billing_address: ?string, billing_city: ?string, billing_state: string, billing_pin: ?string, billing_gstin: ?string}
     */
    private function customerFrom(Request $request): array
    {
        $billing = [
            'billing_address' => $request->filled('billing_address') ? $request->string('billing_address')->toString() : null,
            'billing_city' => $request->filled('billing_city') ? $request->string('billing_city')->toString() : null,
            'billing_state' => $request->string('billing_state')->toString(),
            'billing_pin' => $request->filled('billing_pin') ? $request->string('billing_pin')->toString() : null,
            'billing_gstin' => $request->filled('billing_gstin') ? $request->string('billing_gstin')->toString() : null,
        ];

        $request->user()?->update($billing);

        return [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            ...$billing,
        ];
    }

    private function paymentError(Throwable $exception): string
    {
        if (app()->isLocal() && $exception->getMessage() !== '') {
            return $exception->getMessage();
        }

        return 'Payment could not be started. Please try again.';
    }

    private function unconfirmed(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Payment could not be confirmed.'], 422);
        }

        return redirect()
            ->route('checkout.create')
            ->withErrors(['payment' => 'Payment could not be confirmed.']);
    }

    /**
     * @param  list<array{service: Service, months: ?int}>  $items
     * @param  array{lines: list<array{gross: int, discount: int, gst: int}>}  $pricing
     * @param  list<int>  $indexes
     * @param  list<int>  $except
     */
    private function matchingPending(Request $request, array $items, array $pricing, array $indexes, array $except): ?Order
    {
        $existing = $this->findPending($request->user()->id, $this->signature($this->rowsFromPricing($items, $pricing, $indexes)), $except);

        $existing?->update($this->customerFrom($request));

        return $existing;
    }

    /**
     * @param  list<int>  $except
     */
    private function findPending(int $userId, string $signature, array $except): ?Order
    {
        return Order::query()
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->whereNull('razorpay_payment_id')
            ->when($except !== [], fn ($query) => $query->whereNotIn('id', $except))
            ->with(['items', 'subscriptions'])
            ->get()
            ->first(function (Order $order) use ($signature): bool {
                if ($order->subscriptions->contains(fn (Subscription $subscription) => $subscription->razorpay_payment_id !== null)) {
                    return false;
                }

                $reusable = $order->razorpay_order_id !== null
                    || $order->subscriptions->contains(fn (Subscription $subscription) => $subscription->razorpay_subscription_id !== null);

                return $reusable && $this->signature($this->rowsFromOrder($order)) === $signature;
            });
    }

    /**
     * @param  list<array{service: Service, months: ?int}>  $items
     * @param  array{lines: list<array{gross: int, discount: int, gst: int}>}  $pricing
     * @param  list<int>  $indexes
     * @return list<array{service_id: ?int, service_name: string, months: ?int, price: int, discount: int, gst: int}>
     */
    private function rowsFromPricing(array $items, array $pricing, array $indexes): array
    {
        return array_map(function (int $index) use ($items, $pricing): array {
            $item = $items[$index];

            return [
                'service_id' => $item['service']->id,
                'service_name' => $item['service']->name,
                'months' => $item['months'],
                'price' => $pricing['lines'][$index]['gross'],
                'discount' => $pricing['lines'][$index]['discount'],
                'gst' => $pricing['lines'][$index]['gst'],
            ];
        }, $indexes);
    }

    /**
     * @return list<array{service_id: ?int, service_name: string, months: ?int, price: float|string, discount: float|string, gst: float|string}>
     */
    private function rowsFromOrder(Order $order): array
    {
        return $order->items->map(fn ($item) => [
            'service_id' => $item->service_id,
            'service_name' => $item->service_name,
            'months' => $item->months,
            'price' => $item->price,
            'discount' => $item->discount,
            'gst' => $item->gst,
        ])->all();
    }

    /**
     * @param  list<array{service_id: ?int, service_name: string, months: ?int, price: float|int|string, discount: float|int|string, gst: float|int|string}>  $rows
     */
    private function signature(array $rows): string
    {
        $parts = array_map(fn (array $row) => implode(':', [
            $row['service_id'] ?? '',
            $row['service_name'],
            $row['months'] ?? '',
            (int) round((float) $row['price']),
            (int) round((float) $row['discount']),
            (int) round((float) $row['gst']),
        ]), $rows);
        sort($parts);

        return implode('|', $parts);
    }

    /**
     * @return array{type: string, order_id?: string, subscription_id?: string, amount?: int, currency?: string, description: string}
     */
    private function stepFrom(Order $order): array
    {
        $subscription = $order->subscriptions->first() ?? $order->subscriptions()->first();

        if ($subscription?->razorpay_subscription_id !== null && $order->razorpay_order_id === null) {
            return [
                'type' => 'subscription',
                'subscription_id' => $subscription->razorpay_subscription_id,
                'description' => $subscription->name,
            ];
        }

        return [
            'type' => 'order',
            'order_id' => (string) $order->razorpay_order_id,
            'amount' => (int) $order->razorpay_amount,
            'currency' => 'INR',
            'description' => 'Order '.$order->number,
        ];
    }

    /**
     * @param  list<int>  $keep
     */
    private function retireUnpaid(Request $request, Razorpay $razorpay, array $keep): void
    {
        $pending = Order::query()
            ->where('user_id', $request->user()->id)
            ->where('status', 'pending')
            ->when($keep !== [], fn ($query) => $query->whereNotIn('id', $keep))
            ->with('subscriptions')
            ->get();

        foreach ($pending as $order) {
            if ($order->razorpay_payment_id !== null) {
                continue;
            }

            if ($order->subscriptions->contains(fn (Subscription $subscription) => $subscription->razorpay_payment_id !== null || $subscription->status === 'active')) {
                continue;
            }

            $alreadyPaid = false;

            foreach ($order->subscriptions as $subscription) {
                if ($subscription->razorpay_subscription_id === null) {
                    continue;
                }

                $remote = $razorpay->fetchSubscription($subscription->razorpay_subscription_id);

                if (in_array($remote['status'] ?? 'created', ['active', 'authenticated'], true)) {
                    $alreadyPaid = true;

                    break;
                }

                try {
                    $razorpay->cancelSubscription($subscription->razorpay_subscription_id);
                } catch (Throwable $exception) {
                    report($exception);
                }
            }

            if (! $alreadyPaid) {
                $order->delete();
            }
        }
    }

    private function releasePaidLines(Request $request, Order $order): void
    {
        if (in_array($request->session()->get('checkout.source'), ['buy', 'plan', 'quote'], true)) {
            return;
        }

        $cart = new Cart($request);

        foreach ($order->items as $item) {
            if ($item->service_id === null) {
                continue;
            }

            $cart->remove($item->service_id.'-'.($item->months ?? 0));
        }
    }

    private function releaseCheckout(Request $request): void
    {
        $source = $request->session()->get('checkout.source');

        if (in_array($source, ['buy', 'plan', 'quote'], true)) {
            $request->session()->forget('checkout');
        } else {
            (new Cart($request))->clear();
            $request->session()->forget('checkout');
        }

        $request->session()->forget('coupon');
    }

    private function number(): string
    {
        do {
            $number = 'SR-'.strtoupper(Str::random(6));
        } while (Order::query()->where('number', $number)->exists());

        return $number;
    }
}
