<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Mail\QuoteReceivedMail;
use App\Models\Quote;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QuoteController extends Controller
{
    public function create(Service $service): View
    {
        abort_unless($service->is_active, 404);

        return view('services.quote', [
            'service' => $service,
        ]);
    }

    public function store(QuoteRequest $request, Service $service): RedirectResponse
    {
        abort_unless($service->is_active, 404);

        $quote = Quote::query()->create([
            ...$request->validated(),
            'service_id' => $service->id,
            'user_id' => $request->user()?->id,
            'status' => 'new',
        ]);

        QuoteReceivedMail::deliver($quote);

        return redirect()
            ->route('services.show', $service)
            ->with('status', 'Quote request sent. Sunrise will reply with a price.');
    }

    public function pay(Request $request, string $token): RedirectResponse
    {
        $quote = Quote::query()
            ->with('service')
            ->where('pay_token', $token)
            ->where('status', 'replied')
            ->whereNotNull('quoted_price')
            ->firstOrFail();

        $paid = $quote->paidOrder();

        if ($paid !== null) {
            if ($request->user() !== null && $paid->user_id === $request->user()->id) {
                return redirect()->route('orders.show', $paid);
            }

            $target = $quote->service?->is_active
                ? route('services.show', $quote->service)
                : route('cart.index');

            return redirect()->to($target)->with('status', 'This quote is already paid.');
        }

        $request->session()->put('checkout', [
            'source' => 'quote',
            'quote_id' => $quote->id,
        ]);

        if ($request->user() === null) {
            $request->session()->put('url.intended', route('checkout.create'));

            return redirect()->route('login');
        }

        return redirect()->route('checkout.create');
    }
}
