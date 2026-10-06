<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Cart;
use App\Support\Wishlist;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\InvalidStateException;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(): RedirectResponse
    {
        if (! config('services.google.client_id') || ! config('services.google.client_secret')) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in is not configured yet.']);
        }

        return Socialite::driver('google')
            ->with(['prompt' => 'select_account'])
            ->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        if ($request->string('error')->toString() === 'access_denied') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'That Google account cannot sign in yet. In Google Cloud, open the OAuth consent screen and add this Gmail address under Test users.']);
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (InvalidStateException) {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'The Google sign-in session expired. Click Sign in with Google again and stay in the same browser tab.']);
        } catch (Throwable $exception) {
            report($exception);

            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google sign-in was cancelled or failed. Try again.']);
        }

        $email = $googleUser->getEmail();

        if (! is_string($email) || $email === '') {
            return redirect()
                ->route('login')
                ->withErrors(['email' => 'Google did not share an email address.']);
        }

        $user = User::query()->where('google_id', $googleUser->getId())->first();

        if (! $user) {
            $user = User::query()->where('email', $email)->first();

            if ($user && $user->google_id && $user->google_id !== $googleUser->getId()) {
                return redirect()
                    ->route('login')
                    ->withErrors(['email' => 'This email is already linked to another Google account.']);
            }

            if ($user) {
                $user->forceFill(['google_id' => $googleUser->getId()])->save();
            } else {
                $user = User::query()->create([
                    'name' => $googleUser->getName() ?: 'Sunrise customer',
                    'email' => $email,
                    'google_id' => $googleUser->getId(),
                ]);

                $user->forceFill(['email_verified_at' => now()])->save();
            }
        }

        $held = Cart::hold(request());
        $saved = Wishlist::hold(request());

        Auth::login($user, true);

        request()->session()->regenerate();

        (new Cart(request()))->resume($held);
        (new Wishlist(request()))->resume($saved);

        return redirect()->intended(route('home'));
    }
}
