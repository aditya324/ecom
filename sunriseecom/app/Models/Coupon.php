<?php

namespace App\Models;

use App\Support\Bill;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

#[Fillable([
    'code',
    'type',
    'amount',
    'per_user_limit',
    'usage_limit',
    'is_active',
])]
class Coupon extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * @return HasMany<CouponUse, $this>
     */
    public function uses(): HasMany
    {
        return $this->hasMany(CouponUse::class);
    }

    public function timesUsed(?int $userId, ?string $email): int
    {
        $email = $email !== null ? strtolower(trim($email)) : null;

        if ($userId === null && ($email === null || $email === '')) {
            return 0;
        }

        return $this->uses()
            ->where(function ($query) use ($userId, $email) {
                if ($userId !== null) {
                    $query->orWhere('user_id', $userId);
                }

                if ($email !== null && $email !== '') {
                    $query->orWhere('email', $email);
                }
            })
            ->count();
    }

    public function hasUsesLeft(): bool
    {
        return $this->uses()->count() < (int) $this->usage_limit;
    }

    public function availableFor(?int $userId, ?string $email): bool
    {
        return $this->hasUsesLeft()
            && $this->timesUsed($userId, $email) < (int) $this->per_user_limit;
    }

    public function rejection(?int $userId, ?string $email): ?string
    {
        if ($this->availableFor($userId, $email)) {
            return null;
        }

        if (! $this->hasUsesLeft()) {
            return 'This coupon has already been used the allowed number of times.';
        }

        return 'You have already used '.$this->code.' the allowed number of times.';
    }

    public function label(): string
    {
        if ($this->type === 'percent') {
            return rtrim(rtrim(number_format((float) $this->amount, 2, '.', ''), '0'), '.').'%';
        }

        return '₹'.number_format((float) $this->amount, 0, '.', ',');
    }

    public static function applied(Request $request): ?self
    {
        $code = $request->session()->get('coupon');

        if (! is_string($code) || $code === '') {
            return null;
        }

        $coupon = static::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->with('services')
            ->first();

        if ($coupon === null) {
            $request->session()->forget('coupon');
        }

        return $coupon;
    }

    /**
     * @param  list<array{service: Service, price: float, quantity: int}>  $items
     */
    public function discountFor(array $items): float
    {
        return (float) Bill::quote($items, $this)['discount'];
    }
}
