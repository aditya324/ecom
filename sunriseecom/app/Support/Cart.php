<?php

namespace App\Support;

use App\Models\CartDrop;
use App\Models\CartItem;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;

class Cart
{
    public function __construct(private Request $request) {}

    /**
     * @return array<string, array{service_id: int, months: ?int, quantity?: int}>
     */
    public function lines(): array
    {
        if (! $this->request->session()->has('cart') && ($user = $this->request->user()) instanceof User) {
            $this->request->session()->put('cart', $this->storedLines($user));
        }

        return $this->applyDrops($this->request->session()->get('cart', []));
    }

    /**
     * @return array<string, array{service_id: int, months: ?int, quantity?: int}>
     */
    public function savedLines(): array
    {
        return $this->request->session()->get('saved', []);
    }

    public function count(): int
    {
        return array_sum(array_map(
            fn (array $line) => max(1, (int) ($line['quantity'] ?? 1)),
            $this->lines(),
        ));
    }

    /**
     * @param  array<string, array{service_id: int, months: ?int, quantity?: int}>  $lines
     * @return list<array{key: string, service: Service, months: ?int, label: string, price: float, compare: ?float, quantity: int}>
     */
    public function resolve(array $lines): array
    {
        $items = [];

        foreach ($lines as $key => $line) {
            $service = Service::query()
                ->with('category')
                ->whereKey($line['service_id'] ?? null)
                ->where('is_active', true)
                ->first();

            $offer = $service?->offer(isset($line['months']) ? (int) $line['months'] : null);

            if ($service === null || $offer === null) {
                continue;
            }

            $items[] = [
                'key' => (string) $key,
                'service' => $service,
                'months' => $offer['months'],
                'label' => $offer['label'],
                'price' => $offer['price'],
                'compare' => $service->compare_price !== null
                    ? (float) $service->compare_price * ($offer['months'] ?? 1)
                    : null,
                'quantity' => max(1, (int) ($line['quantity'] ?? 1)),
            ];
        }

        return $items;
    }

    public function add(Service $service, ?int $months): void
    {
        $lines = $this->lines();
        $key = $service->id.'-'.($months ?? 0);
        $quantity = min(99, (int) ($lines[$key]['quantity'] ?? 0) + 1);
        $lines[$key] = [
            'service_id' => $service->id,
            'months' => $months,
            'quantity' => $quantity,
        ];

        $this->request->session()->put('cart', $lines);
        $this->persist();
    }

    public function updateQuantity(string $key, int $quantity): void
    {
        $lines = $this->lines();

        if (! isset($lines[$key])) {
            return;
        }

        $lines[$key]['quantity'] = max(1, min(99, $quantity));
        $this->request->session()->put('cart', $lines);
        $this->persist();
    }

    public function remove(string $key): void
    {
        $lines = $this->lines();
        unset($lines[$key]);
        $this->request->session()->put('cart', $lines);
        $this->persist();
    }

    public function saveForLater(string $key): void
    {
        $lines = $this->lines();

        if (! isset($lines[$key])) {
            return;
        }

        $saved = $this->savedLines();
        $saved[$key] = $lines[$key];
        unset($lines[$key]);

        $this->request->session()->put('cart', $lines);
        $this->request->session()->put('saved', $saved);
        $this->persist();
    }

    public function moveToCart(string $key): void
    {
        $saved = $this->savedLines();

        if (! isset($saved[$key])) {
            return;
        }

        $lines = $this->lines();
        $incoming = max(1, (int) ($saved[$key]['quantity'] ?? 1));
        $existing = (int) ($lines[$key]['quantity'] ?? 0);
        $saved[$key]['quantity'] = min(99, $existing + $incoming);
        $lines[$key] = $saved[$key];
        unset($saved[$key]);

        $this->request->session()->put('cart', $lines);
        $this->request->session()->put('saved', $saved);
        $this->persist();
    }

    public function removeSaved(string $key): void
    {
        $saved = $this->savedLines();
        unset($saved[$key]);
        $this->request->session()->put('saved', $saved);
    }

    public function clear(): void
    {
        $this->request->session()->put('cart', []);
        $this->persist();
    }

    /**
     * Keep the cart that was in the session before login, and fold in anything this customer already had saved.
     *
     * @param  array<string, array{service_id: int, months: ?int, quantity?: int}>  $sessionCart
     */
    /**
     * @return array{cart: array<string, array{service_id: int, months: ?int, quantity?: int}>, saved: array<string, array{service_id: int, months: ?int, quantity?: int}>}
     */
    public static function hold(Request $request): array
    {
        return [
            'cart' => $request->session()->get('cart', []),
            'saved' => $request->session()->get('saved', []),
        ];
    }

    /**
     * @param  array{cart?: array<string, array{service_id: int, months: ?int, quantity?: int}>, saved?: array<string, array{service_id: int, months: ?int, quantity?: int}>}  $held
     */
    public function resume(array $held): void
    {
        $this->request->session()->put('saved', $held['saved'] ?? []);
        $this->adopt($held['cart'] ?? []);
    }

    public function adopt(array $sessionCart): void
    {
        $user = $this->request->user();

        if ($user === null) {
            return;
        }

        foreach ($this->storedLines($user) as $key => $line) {
            if (! isset($sessionCart[$key])) {
                $sessionCart[$key] = $line;
            }
        }

        $this->request->session()->put('cart', $sessionCart);
        $this->persist();
    }

    /**
     * @return array<string, array{service_id: int, months: ?int, quantity: int}>
     */
    private function storedLines(User $user): array
    {
        $lines = [];

        foreach (CartItem::query()->where('user_id', $user->id)->get() as $item) {
            $key = $item->service_id.'-'.($item->months ?? 0);
            $lines[$key] = [
                'service_id' => $item->service_id,
                'months' => $item->months,
                'quantity' => max(1, (int) $item->quantity),
            ];
        }

        return $lines;
    }

    /**
     * @param  array<string, array{service_id: int, months: ?int, quantity?: int}>  $lines
     * @return array<string, array{service_id: int, months: ?int, quantity?: int}>
     */
    private function applyDrops(array $lines): array
    {
        $user = $this->request->user();

        if ($user === null) {
            return $lines;
        }

        $keys = CartDrop::query()->where('user_id', $user->id)->pluck('cart_key');

        if ($keys->isEmpty()) {
            return $lines;
        }

        foreach ($keys as $key) {
            unset($lines[$key]);
        }

        $this->request->session()->put('cart', $lines);
        CartDrop::query()->where('user_id', $user->id)->whereIn('cart_key', $keys)->delete();
        $this->persistLines($lines);

        return $lines;
    }

    private function persist(): void
    {
        $this->persistLines($this->request->session()->get('cart', []));
    }

    /**
     * @param  array<string, array{service_id: int, months: ?int, quantity?: int}>  $lines
     */
    private function persistLines(array $lines): void
    {
        $user = $this->request->user();

        if ($user === null) {
            return;
        }

        CartItem::query()->where('user_id', $user->id)->delete();

        foreach ($lines as $line) {
            CartItem::query()->create([
                'user_id' => $user->id,
                'service_id' => $line['service_id'],
                'months' => $line['months'] ?? null,
                'quantity' => max(1, (int) ($line['quantity'] ?? 1)),
            ]);
        }
    }
}
