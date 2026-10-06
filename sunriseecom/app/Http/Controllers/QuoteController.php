<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuoteRequest;
use App\Models\Quote;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
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

        Quote::query()->create([
            ...$request->validated(),
            'service_id' => $service->id,
            'user_id' => $request->user()?->id,
            'status' => 'new',
        ]);

        return redirect()
            ->route('services.show', $service)
            ->with('status', 'Quote request sent. Sunrise will reply with a price.');
    }
}
