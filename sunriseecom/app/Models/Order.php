<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'number',
    'user_id',
    'subscription_id',
    'quote_id',
    'name',
    'email',
    'billing_address',
    'billing_city',
    'billing_state',
    'billing_pin',
    'billing_gstin',
    'status',
    'customer_note',
    'razorpay_order_id',
    'razorpay_payment_id',
    'razorpay_amount',
    'coupon_code',
    'discount',
    'gst',
    'total',
    'refunded_amount',
    'refunded_payments',
])]
class Order extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount' => 'decimal:2',
            'gst' => 'decimal:2',
            'total' => 'decimal:2',
            'refunded_amount' => 'decimal:2',
            'refunded_payments' => 'array',
        ];
    }

    /**
     * Each captured Razorpay payment on this order, so a subscription can be
     * refunded without refunding a one-time payment on the same order.
     *
     * @return list<array{key: string, label: string, payment_id: string, amount: int, refunded: bool}>
     */
    public function paymentParts(): array
    {
        $this->loadMissing(['items', 'subscriptions']);

        $refundedIds = $this->refunded_payments ?? [];
        $subscriptions = $this->subscriptions->filter(fn (Subscription $subscription) => $subscription->razorpay_payment_id !== null);
        $subscriptionPaymentIds = $subscriptions->pluck('razorpay_payment_id')->all();
        $parts = [];

        $onceIsSeparate = $this->razorpay_payment_id !== null
            && ! in_array($this->razorpay_payment_id, $subscriptionPaymentIds, true);

        if ($onceIsSeparate || ($this->razorpay_payment_id !== null && $subscriptions->isEmpty())) {
            $names = $this->items->pluck('service_name')
                ->reject(fn (string $name) => $subscriptions->contains('name', $name))
                ->implode(', ');
            $amount = $subscriptions->isEmpty()
                ? (int) round((float) $this->total * 100)
                : (int) ($this->razorpay_amount ?: round(((float) $this->total - (float) $subscriptions->sum('cycle_amount')) * 100));

            $parts[] = [
                'key' => 'once',
                'label' => $names !== '' ? $names : 'One-time payment',
                'payment_id' => $this->razorpay_payment_id,
                'amount' => max(0, $amount),
                'refunded' => in_array($this->razorpay_payment_id, $refundedIds, true),
            ];
        }

        foreach ($subscriptions as $subscription) {
            $parts[] = [
                'key' => 'subscription-'.$subscription->id,
                'label' => $subscription->name,
                'payment_id' => $subscription->razorpay_payment_id,
                'amount' => (int) round((float) $subscription->cycle_amount * 100),
                'refunded' => in_array($subscription->razorpay_payment_id, $refundedIds, true),
            ];
        }

        return $parts;
    }

    /**
     * @return list<string>
     */
    public static function settledStatuses(): array
    {
        return ['placed', 'in_progress', 'delivered', 'refunded'];
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Waiting for payment',
            'placed' => 'Placed',
            'in_progress' => 'In progress',
            'delivered' => 'Delivered',
            'refunded' => 'Refunded',
            'cancelled' => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    public function recordRefund(string $paymentId, int $paise): bool
    {
        $ids = $this->refunded_payments ?? [];

        if (in_array($paymentId, $ids, true)) {
            return false;
        }

        $ids[] = $paymentId;
        $refunded = round((float) $this->refunded_amount + ($paise / 100), 2);
        $covered = collect($this->paymentParts())->every(
            fn (array $part) => $part['amount'] < 100 || in_array($part['payment_id'], $ids, true)
        );

        $this->update([
            'refunded_payments' => $ids,
            'refunded_amount' => $refunded,
            'status' => $covered || $refunded + 0.001 >= (float) $this->total ? 'refunded' : $this->status,
        ]);

        return true;
    }

    public function getRouteKeyName(): string
    {
        return 'number';
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasMany<Subscription, $this>
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * The subscription a renewal charge belongs to. The subscription row itself
     * stays on the first order.
     *
     * @return BelongsTo<Subscription, $this>
     */
    public function linkedSubscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class, 'subscription_id');
    }

    /**
     * @return BelongsTo<Quote, $this>
     */
    public function quote(): BelongsTo
    {
        return $this->belongsTo(Quote::class);
    }

    /**
     * @return Collection<int, Subscription>
     */
    public function manageableSubscriptions(): Collection
    {
        $this->loadMissing(['subscriptions', 'linkedSubscription']);

        return $this->subscriptions
            ->filter(fn (Subscription $subscription) => $subscription->canManage())
            ->when(
                $this->linkedSubscription?->canManage(),
                fn (Collection $subscriptions) => $subscriptions->push($this->linkedSubscription),
            )
            ->unique('id')
            ->values();
    }

    public function money(): string
    {
        return '₹'.number_format((float) $this->total, 0, '.', ',');
    }

    public static function collected(): float
    {
        return (float) static::query()
            ->whereIn('status', static::settledStatuses())
            ->selectRaw('COALESCE(SUM(total - refunded_amount), 0) as collected')
            ->value('collected');
    }

    public static function formatMoney(float $amount): string
    {
        return '₹'.number_format($amount, 0, '.', ',');
    }
}
