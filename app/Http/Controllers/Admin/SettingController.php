<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ImageService;
use App\Services\TotpService;
use App\Support\FontFamilies;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        $fontOptions = FontFamilies::options();
        $fonts = FontFamilies::all();
        return view('admin.settings.index', compact('settings', 'fontOptions', 'fonts'));
    }

    /**
     * Clear all compiled Blade views so every layout/view recompiles on next request.
     * On shared hosting with OPcache, stale compiled views are the #1 reason
     * settings changes (font, hero image, logo, etc.) don't appear visually.
     */
    private function clearCompiledViews(): void
    {
        $dir = storage_path('framework/views');
        if (!is_dir($dir)) return;
        foreach (glob($dir . '/*.php') as $file) {
            @unlink($file);
        }
        // Bust OPcache so PHP re-reads the fresh compiled views
        if (function_exists('opcache_reset')) {
            @opcache_reset();
        }
    }

    public function update(Request $request)
    {
        $request->validate([
            'site_name' => 'nullable|string|max:100',
            'site_tagline' => 'nullable|string|max:255',
            'site_description' => 'nullable|string|max:500',
            'site_keywords' => 'nullable|string|max:500',
            'contact_email' => 'nullable|email',
            'footer_copyright' => 'nullable|string|max:255',
            // Font
            'site_font_family' => 'nullable|string|in:'.implode(',', array_keys(FontFamilies::all())),
            // Hero
            'hero_phrase_1' => 'nullable|string|max:80',
            'hero_phrase_2' => 'nullable|string|max:80',
            'hero_subtitle' => 'nullable|string|max:500',
            'hero_search_placeholder' => 'nullable|string|max:120',
            // Ads
            'ad_paragraph_frequency' => 'nullable|integer|min:1|max:10',
            // Social media (footer)
            'social_enabled' => 'nullable|in:1',
            'social_x' => 'nullable|string|max:255',
            'social_facebook' => 'nullable|string|max:255',
            'social_pinterest' => 'nullable|string|max:255',
            'social_linkedin' => 'nullable|string|max:255',
            'social_whatsapp' => 'nullable|string|max:255',
            'social_youtube' => 'nullable|string|max:255',
            'social_instagram' => 'nullable|string|max:255',
            // Integrations / SEO
            'ga_measurement_id' => 'nullable|string|max:32',
            'search_console_token' => 'nullable|string|max:255',
            'ahrefs_verification_token' => 'nullable|string|max:255',
            'ads_txt_content' => 'nullable|string|max:20000',
            'robots_txt_content' => 'nullable|string|max:20000',
            'llms_txt_content' => 'nullable|string|max:20000',
        ]);

        $keys = [
            'site_name', 'site_tagline', 'site_description', 'site_keywords', 'contact_email',
            'footer_copyright', 'site_logo',
            'site_font_family',
            'hero_phrase_1', 'hero_phrase_2', 'hero_subtitle', 'hero_search_placeholder',
            'ad_paragraph_frequency',
            // Footer social media. social_enabled is special-cased below
            // because unchecked checkboxes don't submit a value.
            'social_x', 'social_facebook', 'social_pinterest',
            'social_linkedin', 'social_whatsapp', 'social_youtube', 'social_instagram',
            'ga_measurement_id', 'search_console_token', 'ahrefs_verification_token',
            'ads_txt_content', 'robots_txt_content', 'llms_txt_content',
        ];
        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, (string) $request->input($key));
            }
        }
        // Checkbox toggle: if absent, the user unchecked it → store '0'.
        Setting::set('social_enabled', $request->boolean('social_enabled') ? '1' : '0');

        // Flush ALL settings cache so every page picks up every change immediately.
        Setting::flushAllCache();

        if ($request->hasFile('site_logo_file')) {
            $path = app(ImageService::class)->optimizeAndStore($request->file('site_logo_file'), 'uploads/settings');
            Setting::set('site_logo', '/storage/'.$path);
        }

        // Branding uploads: logo (light mode), logo (dark mode), favicon.
        // Each upload:
        //   1. validates file is a real image and within size limit
        //   2. deletes the previously stored file (housekeeping)
        //   3. optimizes (resize + WebP conversion via ImageService)
        //   4. stores the new relative path on the public disk
        foreach (['site_logo_light', 'site_logo_dark', 'site_favicon'] as $fileKey) {
            if ($request->hasFile($fileKey.'_file')) {
                $request->validate([$fileKey.'_file' => 'image|max:2048']);
                $old = Setting::where('key', $fileKey)->value('value');
                if ($old) { \Illuminate\Support\Facades\Storage::disk('public')->delete($old); }
                $path = app(ImageService::class)->optimizeAndStore($request->file($fileKey.'_file'), 'uploads/settings');
                Setting::set($fileKey, $path);
            }
        }

        // Hero person image upload (auto-optimised via ImageService).
        if ($request->boolean('hero_remove_image')) {
            $old = Setting::where('key', 'hero_person_image')->value('value');
            if ($old) { \Illuminate\Support\Facades\Storage::disk('public')->delete($old); }
            Setting::set('hero_person_image', '');
        } elseif ($request->hasFile('hero_person_image_file')) {
            $request->validate(['hero_person_image_file' => 'image|max:4096']);
            $old = Setting::where('key', 'hero_person_image')->value('value');
            if ($old) { \Illuminate\Support\Facades\Storage::disk('public')->delete($old); }
            $path = app(ImageService::class)->optimizeAndStore($request->file('hero_person_image_file'), 'uploads/hero');
            Setting::set('hero_person_image', $path);
        }

        // *** CRITICAL: Clear compiled Blade views + OPcache ***
        // Without this, settings changes (font, hero image, logo, etc.)
        // remain invisible on shared hosting because OPcache serves stale
        // compiled Blade templates. The settings cache (30s TTL) is not
        // enough — the Blade template itself is cached at the PHP opcode level.
        $this->clearCompiledViews();

        return back()->with('success', 'Settings updated');
    }

    // ---------------------------------------------------------------------
    // Two-Factor Authentication (real TOTP)
    // ---------------------------------------------------------------------

    public function security()
    {
        $user = auth()->user();
        $setupSecret = session('2fa_setup_secret');
        $qrUrl = $setupSecret ? TotpService::getQrUrl($user->email, $setupSecret) : null;
        return view('admin.settings.security', compact('user', 'setupSecret', 'qrUrl'));
    }

    public function start2FA(Request $request)
    {
        if (auth()->user()->google2fa_secret) {
            return redirect()->route('admin.settings.security');
        }
        $secret = TotpService::generateSecret();
        session(['2fa_setup_secret' => $secret]);
        return redirect()->route('admin.settings.security')->with('success', 'Scan the QR code with your authenticator app, then confirm with a 6-digit code.');
    }

    public function confirm2FA(Request $request)
    {
        $request->validate(['two_factor_code' => 'required|digits:6']);
        $secret = session('2fa_setup_secret');
        if (!$secret) {
            return redirect()->route('admin.settings.security');
        }
        if (!TotpService::verify($secret, $request->two_factor_code)) {
            return back()->withErrors(['two_factor_code' => 'Invalid code. Check your authenticator app and try again.']);
        }
        $user = auth()->user();
        $user->google2fa_secret = $secret;
        $user->two_factor_enabled = true;
        $user->save();
        session()->forget('2fa_setup_secret');
        $this->clearCompiledViews();
        return redirect()->route('admin.settings.security')->with('success', 'Two-factor authentication enabled.');
    }

    public function disable2FA(Request $request)
    {
        $user = auth()->user();
        $user->google2fa_secret = null;
        $user->two_factor_enabled = false;
        $user->save();
        session()->forget('2fa_setup_secret');
        return redirect()->route('admin.settings.security')->with('success', 'Two-factor authentication disabled.');
    }

    // ---------------------------------------------------------------------
    // SMTP / Email configuration (admin-configurable, no .env edits)
    // ---------------------------------------------------------------------

    /**
     * Persist SMTP configuration to the settings table.
     *
     * The settings are picked up at runtime by AppServiceProvider::boot()
     * via overrideMailConfigFromSettings() — so on the next request the new
     * SMTP values take effect (and Mail::to()->send() uses them).
     *
     * Password handling: if the submitted password field is empty, we keep
     * the existing stored password (so the admin doesn't have to retype it
     * every time they edit another field). Setting "Remove password"
     * checkbox explicitly clears it.
     */
    public function updateSmtp(Request $request)
    {
        $request->validate([
            'mail_mailer'        => 'nullable|string|in:smtp,log,sendmail,array',
            'mail_host'          => 'nullable|string|max:255',
            'mail_port'          => 'nullable|integer|min:1|max:65535',
            'mail_username'      => 'nullable|string|max:255',
            'mail_password'      => 'nullable|string|max:255',
            'mail_encryption'    => 'nullable|string|in:tls,ssl,none',
            'mail_from_address'  => 'nullable|email|max:255',
            'mail_from_name'      => 'nullable|string|max:255',
            'mail_remove_password' => 'nullable|in:1',
        ]);

        // Persist scalar settings.
        Setting::set('mail_mailer', (string) $request->input('mail_mailer', ''), 'text', 'email');
        Setting::set('mail_host', (string) $request->input('mail_host', ''), 'text', 'email');
        Setting::set('mail_port', (string) $request->input('mail_port', ''), 'text', 'email');
        Setting::set('mail_username', (string) $request->input('mail_username', ''), 'text', 'email');
        Setting::set('mail_encryption', (string) $request->input('mail_encryption', ''), 'text', 'email');
        Setting::set('mail_from_address', (string) $request->input('mail_from_address', ''), 'text', 'email');
        Setting::set('mail_from_name', (string) $request->input('mail_from_name', ''), 'text', 'email');

        // Password: explicit "remove" beats everything; else if input empty, keep existing.
        if ($request->boolean('mail_remove_password')) {
            Setting::set('mail_password', '', 'text', 'email');
        } elseif ($request->filled('mail_password')) {
            Setting::set('mail_password', (string) $request->input('mail_password'), 'text', 'email');
        }
        // else: keep existing.

        // Flush settings cache + compiled views so changes are immediate
        Setting::flushAllCache();
        $this->clearCompiledViews();

        return redirect()
            ->route('admin.settings.index', ['tab' => 'email'])
            ->with('success', 'SMTP settings saved. Next outgoing email will use these values.');
    }

    /**
     * Send a test email to verify SMTP config works.
     *
     * Re-reads the latest settings table values via the runtime override
     * in AppServiceProvider (which has already run for this request), then
     * tries to send a plain test email to the address the admin typed.
     *
     * Failures are surfaced as errors (so the admin can see the SMTP
     * rejection reason — e.g. "auth failed", "connection refused").
     */
    public function testEmail(Request $request)
    {
        $validated = $request->validate([
            'test_email_to' => 'required|email|max:255',
        ]);

        try {
            Mail::raw(
                "This is a test email from your Huvanti admin panel.\n\n"
                . "If you're reading this, your SMTP configuration is working correctly.\n\n"
                . "Mailer: " . Config::get('mail.default') . "\n"
                . "Host: " . Config::get('mail.mailers.smtp.host') . "\n"
                . "Port: " . Config::get('mail.mailers.smtp.port') . "\n"
                . "Username: " . Config::get('mail.mailers.smtp.username') . "\n"
                . "From: " . Config::get('mail.from.address'),
                function (Message $message) use ($validated) {
                    $message
                        ->to($validated['test_email_to'])
                        ->subject('Huvanti SMTP test — ' . now()->format('Y-m-d H:i'));
                }
            );

            return back()
                ->with('success', "Test email sent to {$validated['test_email_to']}. Check the inbox (and spam folder).");
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', 'SMTP test failed: ' . $e->getMessage());
        }
    }
}
