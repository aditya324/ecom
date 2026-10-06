<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'order_id',
    'name',
    'period',
    'total_count',
    'paid_count',
    'cycle_amount',
    'status',
    'razorpay_plan_id',
    'razorpay_subscription_id',
    'razorpay_payment_id',
    'short_url',
])]
class Subscription extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cycle_amount' => 'decimal:2',
            'paid_count' => 'integer',
        ];
    }

    public function canManage(): bool
    {
        return in_array($this->status, ['active', 'past_due', 'halted'], true);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'active' => 'Active',
            'past_due' => 'Payment failed',
            'halted' => 'Halted',
            'cancelled' => 'Cancelled',
            'completed' => 'Completed',
            default => 'Pending',
        };
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
