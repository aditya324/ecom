<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class Razorpay
{
    public function createOrder(Order $order, ?int $amount = null): string
    {
        if ($this->key() === '' || $this->secret() === '') {
            throw new RuntimeException('Razorpay is not configured.');
        }

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post('https://api.razorpay.com/v1/orders', [
                'amount' => $amount ?? $this->paise($order),
                'currency' => 'INR',
                'receipt' => $order->number,
            ]);

        $id = $response->json('id');

        if (! $response->successful() || ! is_string($id) || $id === '') {
            $description = $response->json('error.description');

            throw new RuntimeException(is_string($description) && $description !== ''
                ? $description
                : 'Razorpay order was not created.');
        }

        return $id;
    }

    public function createPlan(string $name, string $period, int $amount): string
    {
        $this->configured();

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post('https://api.razorpay.com/v1/plans', [
                'period' => $period,
                'interval' => 1,
                'item' => [
                    'name' => Str::limit($name, 80, ''),
                    'amount' => $amount,
                    'currency' => 'INR',
                    'description' => Str::limit($name, 250, ''),
                ],
            ]);

        return $this->idFrom($response, 'Razorpay plan was not created.');
    }

    /**
     * @return array{id: string, short_url: ?string}
     */
    public function createSubscription(string $planId, int $totalCount): array
    {
        $this->configured();

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post('https://api.razorpay.com/v1/subscriptions', [
                'plan_id' => $planId,
                'total_count' => max(1, $totalCount),
                'customer_notify' => 1,
            ]);

        $shortUrl = $response->json('short_url');

        return [
            'id' => $this->idFrom($response, 'Razorpay subscription was not created.'),
            'short_url' => is_string($shortUrl) && $shortUrl !== '' ? $shortUrl : null,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchSubscription(string $id): array
    {
        $this->configured();

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->timeout(15)
            ->get('https://api.razorpay.com/v1/subscriptions/'.$id);

        $body = $response->json();

        return $response->successful() && is_array($body) ? $body : [];
    }

    public function refundPayment(string $paymentId, int $amount): void
    {
        $this->configured();

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post('https://api.razorpay.com/v1/payments/'.$paymentId.'/refund', [
                'amount' => $amount,
            ]);

        if ($response->successful()) {
            return;
        }

        $description = $response->json('error.description');

        throw new RuntimeException(is_string($description) && $description !== ''
            ? $description
            : 'Razorpay payment was not refunded.');
    }

    public function cancelSubscription(string $id): void
    {
        $this->configured();

        $response = Http::withBasicAuth($this->key(), $this->secret())
            ->acceptJson()
            ->asJson()
            ->timeout(15)
            ->post('https://api.razorpay.com/v1/subscriptions/'.$id.'/cancel', [
                'cancel_at_cycle_end' => 0,
            ]);

        if ($response->successful() || in_array($response->status(), [400, 404], true)) {
            return;
        }

        $description = $response->json('error.description');

        throw new RuntimeException(is_string($description) && $description !== ''
            ? $description
            : 'Razorpay subscription was not cancelled.');
    }

    public function signatureValid(string $orderId, string $paymentId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $orderId.'|'.$paymentId, $this->secret());

        return hash_equals($expected, $signature);
    }

    public function subscriptionSignatureValid(string $paymentId, string $subscriptionId, string $signature): bool
    {
        $expected = hash_hmac('sha256', $paymentId.'|'.$subscriptionId, $this->secret());

        return hash_equals($expected, $signature);
    }

    public function webhookValid(string $body, string $signature): bool
    {
        if ($signature === '' || $this->secret() === '') {
            return false;
        }

        $secret = (string) config('services.razorpay.webhook_secret');

        $expected = hash_hmac('sha256', $body, $secret !== '' ? $secret : $this->secret());

        return hash_equals($expected, $signature);
    }

    public function paise(Order $order): int
    {
        return (int) round(((float) $order->total) * 100);
    }

    public function key(): string
    {
        return (string) config('services.razorpay.key');
    }

    private function configured(): void
    {
        if ($this->key() === '' || $this->secret() === '') {
            throw new RuntimeException('Razorpay is not configured.');
        }
    }

    private function idFrom(Response $response, string $fallback): string
    {
        $id = $response->json('id');

        if (! $response->successful() || ! is_string($id) || $id === '') {
            $description = $response->json('error.description');

            throw new RuntimeException(is_string($description) && $description !== ''
                ? $description
                : $fallback);
        }

        return $id;
    }

    private function secret(): string
    {
        return (string) config('services.razorpay.secret');
    }
}
