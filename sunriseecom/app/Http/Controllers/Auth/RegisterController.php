<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Support\Cart;
use App\Support\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function create(): Response
    {
        return response()
            ->view('auth.register')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $held = Cart::hold($request);
        $saved = Wishlist::hold($request);

        $user = User::create($request->safe()->only(['name', 'email', 'password']));

        Auth::login($user);

        $request->session()->regenerate();

        (new Cart($request))->resume($held);
        (new Wishlist($request))->resume($saved);

        return redirect()->intended(route('home'));
    }
}
