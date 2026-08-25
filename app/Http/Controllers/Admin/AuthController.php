<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\TotpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'two_factor_code' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        if ($user->role !== 'admin') {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        // Real TOTP two-factor check
        if ($user->google2fa_secret) {
            if (!$request->filled('two_factor_code')) {
                return back()->with('show_2fa', true)->withInput($request->only('email'));
            }
            if (!TotpService::verify($user->google2fa_secret, $request->two_factor_code)) {
                return back()->withErrors(['two_factor_code' => 'Invalid authentication code.'])
                    ->with('show_2fa', true)->withInput($request->only('email'));
            }
        }

        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // A fresh admin login always starts in admin mode (never carry over
        // a stale "browsing as user" switch from a previous session).
        $request->session()->forget('acting_role');

        return redirect()->intended(route('admin.dashboard'))->with('success', 'Welcome back!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }
}
