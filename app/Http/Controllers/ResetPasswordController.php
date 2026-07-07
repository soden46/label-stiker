<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\View\View;

class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token): View
    {
        return view('auth.reset-password', ['token' => $token, 'email' => $request->string('email')->toString()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'], 'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $portal = 'backoffice';
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use (&$portal) {
                $portal = $user->portal;
                $user->forceFill(['password' => Hash::make($password), 'remember_token' => Str::random(60)])->save();
                event(new PasswordReset($user));
            },
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route($portal === 'pos' ? 'pos.login' : 'login')->with('status', __($status))
            : back()->withErrors(['email' => __($status)]);
    }
}
