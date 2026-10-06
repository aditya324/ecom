<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Support\AdminFilter;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => AdminFilter::text($request, 'q'),
            'status' => AdminFilter::choice($request, 'status', ['new', 'read']),
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
}
