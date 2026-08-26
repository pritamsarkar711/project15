<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\User;
use App\Services\ImageService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Password;
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
 *   GET  /forgot-password    -> showForgotPasswordForm()
 *   POST /forgot-password    -> sendResetLink()
 *   GET  /reset-password/{token}  -> showResetForm()
 *   POST /reset-password     -> reset()
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

    // ──────────────────────────────────────────────────────────────
    //  Password reset flow (frontend users — not /manage admins).
    //  Standard Laravel broker pattern, using our ResetPassword
    //  notification override on the User model.
    // ──────────────────────────────────────────────────────────────

    public function showForgotPasswordForm()
    {
        if (Auth::check()) {
            return $this->redirectAfterAuth();
        }
        return view('frontend.auth.forgot-password');
    }

    public function sendResetLink(Request $request)
    {
        $request->validate(['email' => ['required', 'email']]);

        $status = Password::broker()->sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
    }

    public function showResetForm(Request $request, $token = null)
    {
        if (Auth::check()) {
            return $this->redirectAfterAuth();
        }
        return view('frontend.auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $validated = $request->validate([
            'token'                 => ['required', 'string'],
            'email'                 => ['required', 'email'],
            'password'              => ['required', 'string', 'min:8', 'max:128', 'confirmed'],
        ], [
            'password.min'      => 'Password must be at least 8 characters.',
            'password.confirmed'=> 'Password confirmation does not match.',
        ]);

        $status = Password::broker()->reset(
            $validated,
            function ($user, $password) use ($request) {
                $user->forceFill([
                    'password'       => $password, // cast to hash in User model
                    'remember_token' => Str::random(60),
                ])->setRememberToken($user->remember_token);
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', __($status))
            : back()->withErrors(['email' => __($status)])->onlyInput('email');
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

    public function redirectToGoogle(Request $request)
    {
        $clientId = trim((string) Setting::get('google_client_id'));
        $enabled = Setting::get('google_enabled') === '1';
        if (!$enabled || !$clientId) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign in is not configured.']);
        }
        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);
        $redirectUri = str_replace('http://', 'https://', url('/auth/google/callback'));
        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'select_account',
        ]);
        return redirect('https://accounts.google.com/o/oauth2/v2/auth?' . $params);
    }

    public function handleGoogleCallback(Request $request)
    {
        $clientId = trim((string) Setting::get('google_client_id'));
        $clientSecret = trim((string) Setting::get('google_client_secret'));
        $enabled = Setting::get('google_enabled') === '1';
        if (!$enabled || !$clientId || !$clientSecret) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign in is not configured.']);
        }
        $state = $request->session()->pull('google_oauth_state');
        if (!$state || $state !== $request->input('state')) {
            return redirect()->route('login')->withErrors(['email' => 'Invalid Google sign in request. Please try again.']);
        }
        if ($request->has('error')) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign in was cancelled.']);
        }
        $code = $request->input('code');
        if (!$code) {
            return redirect()->route('login')->withErrors(['email' => 'Google did not return an authorization code.']);
        }
        try {
            $redirectUri = str_replace('http://', 'https://', url('/auth/google/callback'));
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]);
            if (!$tokenResponse->successful()) {
                throw new \RuntimeException('Failed to exchange code for tokens: ' . $tokenResponse->body());
            }
            $tokens = $tokenResponse->json();
            $accessToken = $tokens['access_token'] ?? null;
            if (!$accessToken) {
                throw new \RuntimeException('No access token returned by Google.');
            }
            $userResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v2/userinfo');
            if (!$userResponse->successful()) {
                throw new \RuntimeException('Failed to fetch Google profile.');
            }
            $gUser = $userResponse->json();
            $googleId = $gUser['id'] ?? null;
            $email = strtolower($gUser['email'] ?? '');
            $name = $gUser['name'] ?? $gUser['given_name'] ?? 'Google User';
            $avatar = $gUser['picture'] ?? null;
            $verified = $gUser['verified_email'] ?? false;
            if (!$googleId || !$email) {
                throw new \RuntimeException('Google did not return email or id.');
            }
            $user = User::where('google_id', $googleId)->first();
            if (!$user) {
                $user = User::where('email', $email)->first();
                if ($user) {
                    $user->forceFill(['google_id' => $googleId, 'avatar' => $avatar ?? $user->avatar])->save();
                } else {
                    $user = User::create([
                        'name' => $name,
                        'email' => $email,
                        'google_id' => $googleId,
                        'avatar' => $avatar,
                        'password' => Str::random(32),
                        'role' => 'author',
                        'email_verified_at' => $verified ? now() : null,
                    ]);
                }
            }
            Auth::login($user, true);
            $request->session()->regenerate();
            return $this->redirectAfterAuth()->with('success', 'Signed in with Google.');
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['email' => 'Google sign in failed: ' . $e->getMessage()]);
        }
    }
}
