<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupportRequest;
use App\Mail\SupportReceivedMail;
use App\Models\SupportMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function create(): View
    {
        return view('support.create', [
            'user' => request()->user(),
        ]);
    }

    public function store(SupportRequest $request): RedirectResponse
    {
        $message = SupportMessage::query()->create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'body' => $request->string('body')->toString(),
            'status' => 'new',
        ]);

        SupportReceivedMail::deliver($message);

        return redirect()
            ->route('support')
            ->with('status', 'Message sent. Sunrise will reply by email.');
    }
}
