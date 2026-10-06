<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfilePasswordRequest;
use App\Http\Requests\ProfileRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', [
            'user' => request()->user(),
        ]);
    }

    public function update(ProfileRequest $request): RedirectResponse
    {
        $request->user()->update($request->safe()->only([
            'name',
            'email',
            'billing_address',
            'billing_city',
            'billing_state',
            'billing_pin',
            'billing_gstin',
        ]));

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Profile saved.');
    }

    public function password(ProfilePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->string('password')->toString(),
        ]);

        return redirect()
            ->route('profile.edit')
            ->with('status', 'Password saved.');
    }

    public function orders(): View
    {
        $user = request()->user();

        return view('profile.orders', [
            'orders' => $user->orders()->whereIn('status', Order::settledStatuses())->with('items')->latest()->get(),
            'subscriptions' => $user->subscriptions()->whereIn('status', ['active', 'past_due', 'halted'])->latest()->get(),
        ]);
    }
}
