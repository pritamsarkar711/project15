<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\ImageService;
use App\Services\TotpService;
use App\Support\FontFamilies;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = Setting::all()->keyBy('key');
        $fontOptions = FontFamilies::options();
        $fonts = FontFamilies::all();
        return view('admin.settings.index', compact('settings', 'fontOptions', 'fonts'));
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
        // The hero is rendered in a circular frame, so ANY image (transparent
        // PNG, JPG with solid bg, etc.) now looks correct — no need for the
        // GD transparency edge-artifact dance.
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
}
