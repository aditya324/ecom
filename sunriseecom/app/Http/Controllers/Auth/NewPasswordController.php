<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class NewPasswordController extends Controller
{
    public function create(Request $request, string $token): Response
    {
        return response()
            ->view('auth.reset-password', [
                'token' => $token,
                'email' => $request->string('email')->toString(),
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate');
    }

    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password): void {
                $user->forceFill(['password' => $password])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => __('This reset link is invalid or has expired.'),
            ])->redirectTo(route('password.reset', [
                'token' => $request->string('token')->toString(),
                'email' => $request->string('email')->toString(),
            ]));
        }

        return redirect()
            ->route('login')
            ->with('status', 'Your password has been reset. You can log in now.');
    }
}
