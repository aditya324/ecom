<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartItemRequest;
use App\Models\Coupon;
use App\Models\Service;
use App\Support\Bill;
use App\Support\Cart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $request->session()->forget('checkout');
        $cart = new Cart($request);
        $items = $this->withDue($cart->resolve($cart->lines()));
        $coupon = Coupon::applied($request);
        $customer = $request->user();

        if ($coupon !== null && $coupon->rejection($customer?->id, $customer?->email) !== null) {
            $request->session()->forget('coupon');
            $coupon = null;
        }

        $bill = Bill::quote($this->billable($items), $coupon);

        if ($coupon !== null && $bill['discount'] <= 0) {
            $request->session()->forget('coupon');
            $coupon = null;
            $bill = Bill::quote($this->billable($items), null);
        }

        return view('cart.index', [
            'items' => $items,
            'saved' => $cart->resolve($cart->savedLines()),
            'coupon' => $coupon,
            'bill' => $bill,
        ]);
    }

    /**
     * @param  list<array{service: Service, months: ?int, price: float}>  $items
     * @return list<array{service: Service, months: ?int, price: float, due: float, cycles: ?int}>
     */
    private function withDue(array $items): array
    {
        return array_map(function (array $item): array {
            $monthly = $item['service']->billing_type === 'monthly';
            $months = max(1, (int) ($item['months'] ?? 1));

            $item['cycles'] = $monthly
                ? (($item['months'] ?? null) === null ? 12 : $months)
                : null;
            $item['due'] = $monthly && ($item['months'] ?? null) !== null && (int) $item['months'] > 1
                ? $item['price'] / (int) $item['months']
                : $item['price'];

            return $item;
        }, $items);
    }

    /**
     * @param  list<array{price: float, due: float}>  $items
     * @return list<array{price: float, due: float}>
     */
    private function billable(array $items): array
    {
        return array_map(function (array $item): array {
            $item['price'] = $item['due'];

            return $item;
        }, $items);
    }

    public function store(CartItemRequest $request, Service $service): RedirectResponse
    {
        abort_unless($service->is_active, 404);

        $months = $request->filled('months') ? $request->integer('months') : null;

        if ($service->offer($months) === null) {
            return back()->withErrors(['months' => 'Choose a plan.']);
        }

        $request->session()->forget('checkout');
        (new Cart($request))->add($service, $months);

        return redirect()
            ->route('cart.index')
            ->with('status', $service->name.' added to your cart.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        (new Cart($request))->remove($request->string('key')->toString());

        return redirect()->route('cart.index');
    }

    public function quantity(Request $request): RedirectResponse
    {
        (new Cart($request))->updateQuantity(
            $request->string('key')->toString(),
            $request->integer('quantity'),
        );

        return redirect()->route('cart.index');
    }

    public function save(Request $request): RedirectResponse
    {
        (new Cart($request))->saveForLater($request->string('key')->toString());

        return redirect()->route('cart.index');
    }

    public function move(Request $request): RedirectResponse
    {
        (new Cart($request))->moveToCart($request->string('key')->toString());

        return redirect()->route('cart.index');
    }

    public function forgetSaved(Request $request): RedirectResponse
    {
        (new Cart($request))->removeSaved($request->string('key')->toString());

        return redirect()->route('cart.index');
    }

    public function coupon(Request $request): RedirectResponse
    {
        $code = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $request->string('code')->toString()));
        $coupon = Coupon::query()
            ->where('code', $code)
            ->where('is_active', true)
            ->with('services')
            ->first();

        if ($coupon === null) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['code' => 'That coupon code is not valid.']);
        }

        $items = (new Cart($request))->resolve((new Cart($request))->lines());

        if ($coupon->discountFor($items) <= 0) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['code' => 'This coupon does not apply to the services in your cart.']);
        }

        $user = $request->user();
        $reason = $coupon->rejection($user?->id, $user?->email);

        if ($reason !== null) {
            return redirect()
                ->route('cart.index')
                ->withErrors(['code' => $reason]);
        }

        $request->session()->put('coupon', $coupon->code);

        return redirect()
            ->route('cart.index')
            ->with('status', 'Coupon '.$coupon->code.' applied.');
    }

    public function forgetCoupon(Request $request): RedirectResponse
    {
        $request->session()->forget('coupon');

        return redirect()->route('cart.index');
    }
}
