<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\Service;

class Bill
{
    /**
     * 18% GST added on top of a price. The catalog price is the amount before tax.
     */
    public static function gstOn(int $taxable): int
    {
        return (int) round($taxable * 18 / 100);
    }

    /**
     * @param  list<array{key?: string, service: Service, price: float, quantity: int}>  $items
     * @return array{
     *     lines: list<array{key: string, gross: int, discount: int, payable: int, gst: int, taxable: int}>,
     *     subtotal: int,
     *     discount: int,
     *     gst: int,
     *     taxable: int,
     *     total: int,
     *     code: ?string
     * }
     */
    public static function quote(array $items, ?Coupon $coupon): array
    {
        $grosses = [];

        foreach ($items as $item) {
            $grosses[] = (int) round($item['price'] * $item['quantity']);
        }

        $discounts = self::discounts($items, $grosses, $coupon);
        $lines = [];

        foreach ($items as $index => $item) {
            $taxable = max(0, $grosses[$index] - $discounts[$index]);
            $gst = self::gstOn($taxable);

            $lines[] = [
                'key' => (string) ($item['key'] ?? $index),
                'gross' => $grosses[$index],
                'discount' => $discounts[$index],
                'payable' => $taxable + $gst,
                'gst' => $gst,
                'taxable' => $taxable,
            ];
        }

        $discount = array_sum(array_column($lines, 'discount'));
        $gst = array_sum(array_column($lines, 'gst'));
        $taxable = array_sum(array_column($lines, 'taxable'));

        return [
            'lines' => $lines,
            'subtotal' => array_sum(array_column($lines, 'gross')),
            'discount' => $discount,
            'gst' => $gst,
            'taxable' => $taxable,
            'total' => $taxable + $gst,
            'code' => $discount > 0 ? $coupon?->code : null,
        ];
    }

    /**
     * @param  list<array{service: Service, price: float, quantity: int}>  $items
     * @param  list<int>  $grosses
     * @return list<int>
     */
    private static function discounts(array $items, array $grosses, ?Coupon $coupon): array
    {
        $discounts = array_fill(0, count($items), 0);

        if ($coupon === null) {
            return $discounts;
        }

        $coupon->loadMissing('services');
        $serviceIds = $coupon->services->pluck('id');
        $eligible = [];

        foreach ($items as $index => $item) {
            if ($serviceIds->contains($item['service']->id) && $grosses[$index] > 0) {
                $eligible[] = $index;
            }
        }

        if ($eligible === []) {
            return $discounts;
        }

        if ($coupon->type === 'percent') {
            foreach ($eligible as $index) {
                $discounts[$index] = min(
                    $grosses[$index],
                    (int) round($grosses[$index] * (float) $coupon->amount / 100),
                );
            }

            return $discounts;
        }

        $eligibleGross = array_sum(array_map(fn (int $index) => $grosses[$index], $eligible));
        $pool = min((int) round((float) $coupon->amount), $eligibleGross);
        $allocated = 0;
        $last = $eligible[array_key_last($eligible)];

        foreach ($eligible as $index) {
            if ($index === $last) {
                $share = min($grosses[$index], $pool - $allocated);
            } else {
                $share = (int) round($pool * $grosses[$index] / $eligibleGross);
                $share = min($share, $grosses[$index], $pool - $allocated);
            }

            $discounts[$index] = $share;
            $allocated += $share;
        }

        return $discounts;
    }
}
