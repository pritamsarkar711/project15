<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Frontend user authentication (separate from /manage admin login).
 *
 * Routes (defined in routes/web.php):
 *   GET  /register           -> showRegisterForm()
 *   POST /register           -> register()
 *   GET  /login              -> showLoginForm()
 *   POST /login              -> login()
 *   POST /logout             -> logout()
 *
 * A registered user has role='author' by default. They get a separate
 * dashboard at /author-dashboard (NOT /manage) where they can submit posts
 * for admin review. Admins (role='admin') are NOT created here — only the
 * installer creates the first admin.
 */
class AuthController extends Controller
{
    public function showRegisterForm()
    {
        if (Auth::check()) {
            return $this->redirectAfterAuth();
        }
        return view('frontend.auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:60', 'regex:/^[\p{L}\p{M}\s.\-]+$/u'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ], [
            'name.regex'    => 'Display name can contain letters, spaces, dots and hyphens only.',
            'email.unique'  => 'An account with this email already exists.',
            'password.min'  => 'Password must be at least 8 characters.',
        ]);

        $user = User::create([
            'name'     => trim($validated['name']),
            'email'    => strtolower($validated['email']),
            'password' => $validated['password'], // cast to hash in User model
            'role'     => 'author',
            // username left null — user must set it from /author-dashboard/profile
            // (it's a one-time-locked field so we don't allow it here).
        ]);

        Auth::login($user, true);
        $request->session()->regenerate();

        return redirect()->route('author.dashboard')
            ->with('success', 'Welcome to Huvanti! Set your username to get started.');
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectAfterAuth();
        }
        return view('frontend.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // The admin login is at /manage/login. This /login is for users/authors
        // only — but admins are also users, so we let them in too and route
        // them to their proper dashboard after auth.
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'We couldn\'t find an account with those details.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return $this->redirectAfterAuth()->with('success', 'Welcome back!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'You\'ve been signed out.');
    }

    /**
     * Send admin users to /manage, author users to /author-dashboard.
     */
    private function redirectAfterAuth()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('author.dashboard');
    }
}
