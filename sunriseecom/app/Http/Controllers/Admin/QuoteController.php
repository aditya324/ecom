<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReturnsToFilteredIndex;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuoteReplyRequest;
use App\Mail\QuoteReplyMail;
use App\Models\Quote;
use App\Support\AdminFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class QuoteController extends Controller
{
    use ReturnsToFilteredIndex;

    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'status' => AdminFilter::choice($request, 'status', ['new', 'replied']),
        ];

        $quotes = Quote::query()->with('service');

        if ($filters['q'] !== '') {
            $term = AdminFilter::like($filters['q']);
            $quotes->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhereHas('service', fn ($service) => $service->where('name', 'like', $term));
            });
        }

        if ($filters['status'] !== '') {
            $quotes->where('status', $filters['status']);
        }

        $all = Quote::query()->get();

        return view('admin.quotes.index', [
            'quotes' => $quotes->latest()->get(),
            'counts' => [
                'all' => $all->count(),
                'waiting' => $all->where('status', 'new')->count(),
            ],
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters),
        ]);
    }

    public function edit(Quote $quote): View
    {
        $quote->load('service');

        return view('admin.quotes.form', [
            'quote' => $quote,
        ]);
    }

    public function update(QuoteReplyRequest $request, Quote $quote): RedirectResponse
    {
        $quote->fill($request->safe()->only([
            'quoted_price',
            'reply',
        ]));
        $quote->status = 'replied';
        $quote->load('service');

        try {
            Mail::to($quote->email)->send(new QuoteReplyMail($quote));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['reply' => 'The email was not sent. The reply was not saved.']);
        }

        $quote->save();

        return redirect()
            ->route('admin.quotes.index')
            ->with('status', 'Reply emailed to '.$quote->email.'.');
    }

    public function destroy(Quote $quote): RedirectResponse
    {
        $quote->delete();

        return $this->filteredIndex('admin.quotes.index', 'Quote deleted.');
    }
}
