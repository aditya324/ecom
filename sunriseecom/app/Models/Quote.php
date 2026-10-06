<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'service_id',
    'user_id',
    'name',
    'email',
    'message',
    'status',
    'quoted_price',
    'reply',
    'pay_token',
])]
class Quote extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quoted_price' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Order, $this>
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function paidOrder(): ?Order
    {
        return $this->orders()->whereIn('status', Order::settledStatuses())->first();
    }

    public static function makePayToken(): string
    {
        do {
            $token = Str::lower(Str::random(40));
        } while (static::query()->where('pay_token', $token)->exists());

        return $token;
    }

    public function money(): ?string
    {
        if ($this->quoted_price === null) {
            return null;
        }

        return '₹'.number_format((float) $this->quoted_price, 0, '.', ',');
    }
}
