<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'portal' => 'backoffice', 'is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Akun Back Office atau password belum cocok.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended($request->user()->homePath());
    }

    public function createPos(): View
    {
        return view('auth.pos-login');
    }

    public function storePos(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::attempt([...$credentials, 'portal' => 'pos', 'is_active' => true], $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Akun POS atau password belum cocok.'])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended('/pos');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $portal = $request->user()?->portal;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route($portal === 'pos' ? 'pos.login' : 'login');
    }
}
