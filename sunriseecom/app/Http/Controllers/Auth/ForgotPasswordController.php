<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function create(): Response
    {
        return response()
            ->view('auth.forgot-password')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function store(ForgotPasswordRequest $request): RedirectResponse
    {
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_THROTTLED) {
            throw ValidationException::withMessages([
                'email' => __('Please wait a minute before requesting another link.'),
            ])->redirectTo(route('password.request'));
        }

        return redirect()
            ->route('password.request')
            ->with('status', 'If an account exists for that email, we sent a reset link.');
    }
}
