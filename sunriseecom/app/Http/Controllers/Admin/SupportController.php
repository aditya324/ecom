<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SupportReplyRequest;
use App\Mail\SupportReplyMail;
use App\Models\SupportMessage;
use App\Support\AdminFilter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Throwable;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'status' => AdminFilter::choice($request, 'status', ['new', 'read', 'replied']),
        ];

        $messages = SupportMessage::query();

        if ($filters['q'] !== '') {
            $term = AdminFilter::like($filters['q']);
            $messages->where(function ($query) use ($term): void {
                $query->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('body', 'like', $term);
            });
        }

        if ($filters['status'] !== '') {
            $messages->where('status', $filters['status']);
        }

        $messages = $messages->latest()->get();

        return view('admin.support.index', [
            'messages' => $messages,
            'filters' => $filters,
            'filtering' => AdminFilter::active($filters),
            'waiting' => SupportMessage::query()->where('status', 'new')->count(),
        ]);
    }

    public function show(SupportMessage $message): View
    {
        if ($message->status === 'new') {
            $message->update(['status' => 'read']);
        }

        return view('admin.support.show', [
            'message' => $message,
        ]);
    }

    public function update(SupportReplyRequest $request, SupportMessage $message): RedirectResponse
    {
        $message->reply = $request->string('reply')->toString();
        $message->status = 'replied';

        try {
            Mail::to($message->email)->send(new SupportReplyMail($message));
        } catch (Throwable $exception) {
            report($exception);

            return back()
                ->withInput()
                ->withErrors(['reply' => 'The email was not sent. The reply was not saved.']);
        }

        $message->save();

        return redirect()
            ->route('admin.support.index')
            ->with('status', 'Reply emailed to '.$message->email.'.');
    }
}
