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
            // Hidden marker telling us which tab submitted the form, so we can
            // (a) scope checkbox handling to the General tab and
            // (b) redirect back to the same tab after saving.
            'tab' => 'nullable|string|max:32',
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
            // Revenue program switch (author panel)
            'revenue_enabled' => 'nullable|in:1',
            // Frontend feature switches
            'top_contributors_enabled' => 'nullable|in:1',
            // Maintenance mode (timer is optional; empty or past = no countdown)
            'maintenance_enabled' => 'nullable|in:1',
            'maintenance_ends_at' => 'nullable|date',
            // Integrations / SEO
            'ga_measurement_id' => 'nullable|string|max:32',
            'gtm_container_id' => 'nullable|string|max:32',
            'search_console_token' => 'nullable|string|max:255',
            'ahrefs_verification_token' => 'nullable|string|max:255',
            'ads_txt_content' => 'nullable|string|max:20000',
            'robots_txt_content' => 'nullable|string|max:20000',
            'llms_txt_content' => 'nullable|string|max:20000',
            // Google OAuth
            'google_client_id' => 'nullable|string|max:255',
            'google_client_secret' => 'nullable|string|max:255',
            'google_enabled' => 'nullable|in:1',
            // Uploads — extensions must match ImageService's capabilities.
            // (HEIC/AVIF are rejected up-front with a clear message instead of
            // crashing with a 500 halfway through the save.) The size caps are
            // deliberately modest so uploads stay below typical shared-hosting
            // post_max_size limits (a larger POST is dropped by PHP before
            // Laravel ever sees it, which used to look like "save did nothing").
            'hero_person_image_file' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp|max:4096',
            'site_logo_light_file'   => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,svg|max:2048',
            'site_logo_dark_file'    => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,svg|max:2048',
            'site_favicon_file'      => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,svg,ico|max:1024',
        ]);

        $keys = [
            'site_name', 'site_tagline', 'site_description', 'site_keywords', 'contact_email',
            'footer_copyright',
            'site_font_family',
            'hero_phrase_1', 'hero_phrase_2', 'hero_subtitle', 'hero_search_placeholder',
            'ad_paragraph_frequency',
            // Footer social media. social_enabled is special-cased below
            // because unchecked checkboxes don't submit a value.
            'social_x', 'social_facebook', 'social_pinterest',
            'social_linkedin', 'social_whatsapp', 'social_youtube', 'social_instagram',
            'ga_measurement_id', 'gtm_container_id', 'search_console_token', 'ahrefs_verification_token',
            'ads_txt_content', 'robots_txt_content', 'llms_txt_content',
            'google_client_id', 'google_client_secret',
        ];
        foreach ($keys as $key) {
            if ($request->has($key)) {
                Setting::set($key, (string) $request->input($key));
            }
        }

        if ($request->input('tab') === 'integrations') {
            Setting::set('google_enabled', $request->boolean('google_enabled') ? '1' : '0');
        }

        // Checkbox toggle — ONLY when the General tab form was submitted.
        // (The Appearance/Hero/Ads/Integrations forms don't contain this
        // checkbox; storing '0' unconditionally used to silently disable the
        // footer social links every time another tab was saved.)
        if ($request->input('tab') === 'general' || $request->has('site_name')) {
            Setting::set('social_enabled', $request->boolean('social_enabled') ? '1' : '0');
            // Feature visibility switches that live on the General tab.
            // Unchecked checkboxes submit nothing, so they are only written
            // when the General form itself was submitted (same reasoning as
            // social_enabled above).
            Setting::set('top_contributors_enabled', $request->boolean('top_contributors_enabled') ? '1' : '0');
            // Maintenance mode + optional countdown end. An empty/absent time
            // clears the timer ("back soon", no countdown). A PAST time is
            // also treated as no timer by the middleware. Flush now so the
            // mode flips for the very next request instead of after the 30s
            // settings cache TTL.
            Setting::set('maintenance_enabled', $request->boolean('maintenance_enabled') ? '1' : '0');
            $endsAtRaw = trim((string) $request->input('maintenance_ends_at', ''));
            try {
                // Past times are ACCEPTED but normalised to empty — otherwise
                // an expired timer left in the field would block every later
                // General-tab save. The middleware ignores past times anyway.
                $endsAt = $endsAtRaw !== '' && \Illuminate\Support\Carbon::parse($endsAtRaw)->isFuture()
                    ? \Illuminate\Support\Carbon::parse($endsAtRaw)->format('Y-m-d H:i:s')
                    : '';
            } catch (\Throwable $e) {
                $endsAt = '';
            }
            Setting::set('maintenance_ends_at', $endsAt);
            Setting::flushAllCache();
        }

        // Revenue program switch and ads master switch — ONLY when the Ads
        // tab form was submitted (the other forms don't contain these
        // checkboxes).
        if ($request->input('tab') === 'ads') {
            Setting::set('revenue_enabled', $request->boolean('revenue_enabled') ? '1' : '0');
            Setting::set('ads_enabled', $request->boolean('ads_enabled') ? '1' : '0');
            Setting::flushAllCache();
        }

        // Flush ALL settings cache so every page picks up every change immediately.
        Setting::flushAllCache();

        $tab = $request->input('tab') ?: 'general';

        // ------------------------------------------------------------------
        // Image uploads. Each one is wrapped so a bad file returns a friendly
        // error message instead of a 500 server error.
        // ------------------------------------------------------------------
        try {
            $imageService = app(ImageService::class);

            // Branding uploads: logo (light mode), logo (dark mode), favicon.
            // SVG logos and .ico favicons are stored as-is (no re-encoding);
            // raster images are optimised + converted to WebP.
            foreach (['site_logo_light', 'site_logo_dark', 'site_favicon'] as $fileKey) {
                if ($request->hasFile($fileKey.'_file')) {
                    // Store NEW first, delete OLD after — a failed upload then
                    // never leaves the site without its current logo/favicon.
                    $path = $imageService->optimizeAndStore(
                        $request->file($fileKey.'_file'), 'uploads/settings', true
                    );
                    $old = Setting::where('key', $fileKey)->value('value');
                    if ($old && $old !== $path) {
                        $imageService->delete($old);
                    }
                    Setting::set($fileKey, $path);
                    Setting::flushAllCache();
                }
            }

            // Hero person image upload (auto-optimised via ImageService).
            if ($request->boolean('hero_remove_image')) {
                $old = Setting::where('key', 'hero_person_image')->value('value');
                $imageService->delete($old);
                Setting::set('hero_person_image', '');
                Setting::flushAllCache();
            } elseif ($request->hasFile('hero_person_image_file')) {
                $path = $imageService->optimizeAndStore(
                    $request->file('hero_person_image_file'), 'uploads/hero'
                );
                $old = Setting::where('key', 'hero_person_image')->value('value');
                if ($old && $old !== $path) {
                    $imageService->delete($old);
                }
                Setting::set('hero_person_image', $path);
                Setting::flushAllCache();
            }
        } catch (\Throwable $e) {
            // Something was wrong with the uploaded file. Everything else has
            // already been saved — show a clear error for the upload itself.
            return redirect()
                ->route('admin.settings.index', ['tab' => $tab])
                ->with('error', 'Upload failed: ' . $e->getMessage());
        }

        // *** CRITICAL: Clear compiled Blade views + OPcache ***
        // Without this, settings changes (font, hero image, logo, etc.)
        // remain invisible on shared hosting because OPcache serves stale
        // compiled Blade templates. The settings cache (30s TTL) is not
        // enough — the Blade template itself is cached at the PHP opcode level.
        $this->clearCompiledViews();

        return redirect()
            ->route('admin.settings.index', ['tab' => $tab])
            ->with('success', 'Settings updated');
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
                        ->subject('SMTP test ' . now()->format('Y-m-d H:i'));
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
