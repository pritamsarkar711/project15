<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('admin.profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,gif,webp,bmp|max:4096',
            // Author profile fields
            'username' => [
                'nullable', 'string', 'max:50', 'regex:/^[a-z0-9._-]+$/i',
                Rule::unique('users')->ignore($user->id),
            ],
            'role_title' => 'nullable|string|max:80',
            'portfolio_url' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
            'social_links.*' => 'nullable|url|max:255',
        ]);

        $user->name = $request->name;
        $user->bio = $request->bio;
        $user->role_title = $request->role_title;
        $user->portfolio_url = $request->portfolio_url;

        // Username: once set, it cannot be changed (we silently ignore
        // subsequent updates so the public /username URL stays stable).
        if (!$user->username && $request->filled('username')) {
            $user->username = strtolower($request->username);
        }

        // Social links — store as JSON, only non-empty URLs.
        if ($request->has('social_links')) {
            $links = [];
            foreach ($request->input('social_links', []) as $platform => $url) {
                $url = trim((string)$url);
                if ($url !== '' && filter_var($url, FILTER_VALIDATE_URL)) {
                    $links[$platform] = $url;
                }
            }
            $user->social_links = $links ?: null;
        }

        // Admins are auto-verified (green badge on profile page).
        if ($user->role === 'admin') {
            $user->is_verified = true;
        }

        // Avatar upload: any processing failure must be a friendly message,
        // never a 500 (Laravel's "image" rule also lets AVIF/HEIC through,
        // which the GD-based optimiser cannot decode). The NEW file is stored
        // first and the old one deleted after, so a failed upload never
        // destroys the existing avatar.
        if ($request->hasFile('avatar')) {
            $newAvatar = null;
            try {
                $newAvatar = app(ImageService::class)->optimizeAndStore($request->file('avatar'), 'uploads/avatars');
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', 'Avatar upload failed: '.$e->getMessage());
            } catch (\Throwable $e) {
                report($e);
                return back()->with('error', 'Avatar upload failed (server storage problem). Your other changes were NOT saved — please try a smaller JPG/PNG image.');
            }
            if ($newAvatar) {
                if ($user->author_avatar_path) {
                    app(\App\Services\ImageService::class)->delete($user->author_avatar_path);
                }
                $user->author_avatar_path = $newAvatar;
            }
        }

        $user->save();

        // Credentials change (email / password) only when current password provided
        $wantsEmail = $request->filled('email') && $request->email !== $user->email;
        $wantsPassword = $request->filled('password');
        if ($wantsEmail || $wantsPassword) {
            $request->validate([
                'current_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect.');
                    }
                }],
                'email' => ['nullable', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
                'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            ]);

            if ($wantsEmail) {
                $user->email = $request->email;
            }
            if ($wantsPassword) {
                $user->password = Hash::make($request->password);
            }
            $user->save();
        }

        return back()->with('success', 'Profile updated');
    }
}
