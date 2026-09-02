<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Notifications\ResetPassword;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;

use App\Models\Category;

#[Fillable(['name', 'email', 'password', 'role', 'status', 'email_verified_at', 'last_login_at', 'bio', 'avatar', 'google_id', 'two_factor_enabled', 'two_factor_secret', 'theme_preference', 'google2fa_secret', 'author_avatar_path', 'username', 'role_title', 'niche', 'panel_font', 'portfolio_url', 'social_links', 'is_verified', 'country'])]
#[Hidden(['password', 'remember_token', 'two_factor_secret', 'google2fa_secret'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'is_verified' => 'boolean',
            'social_links' => 'array',
            'followers_count' => 'integer',
            'following_count' => 'integer',
        ];
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // -----------------------------------------------------------------
    // Role helpers + Admin ⇄ User switch
    // -----------------------------------------------------------------

    /** The real, database-stored role ('admin' | 'author' | 'reader'). */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Count of PUBLISHED posts by this author (used for badges and stats).
     */
    public function publishedPostsCount(): int
    {
        try {
            return (int) $this->publishedPosts()->count();
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Achievement badge shown next to the author's name.
     *
     *   admin  → purple badge  (site administrator)
     *   top    → yellow badge  (100+ published posts, authors only)
     *   author → green badge   (10+ published posts, authors only)
     *   null   → no badge yet
     */
    public function badgeType(): ?string
    {
        if ($this->role === 'admin') {
            return 'admin';
        }
        $count = $this->publishedPostsCount();
        if ($count >= 100) {
            return 'top';
        }
        if ($count >= 10) {
            return 'author';
        }
        return null;
    }

    /**
     * Reusable verified badge markup shown next to the author's name
     * everywhere a name appears (post bylines, author boxes, profiles,
     * panel sidebars). Solid checkmark seal, readable in light AND dark
     * mode. Returns an empty string when the author has no badge.
     */
    public function badgeHtml(): string
    {
        $type = $this->badgeType();
        if ($type === null) {
            return '';
        }

        // Verified checkmark seal (same shape for every level, color differs).
        $seal = '<svg class="w-3 h-3 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path fill-rule="evenodd" d="M8.603 3.799A4.49 4.49 0 0 1 12 2.25c1.357 0 2.573.6 3.397 1.549a4.49 4.49 0 0 1 3.498 1.307 4.491 4.491 0 0 1 1.307 3.497A4.49 4.49 0 0 1 21.75 12a4.49 4.49 0 0 1-1.549 3.397 4.491 4.491 0 0 1-1.307 3.497 4.491 4.491 0 0 1-3.497 1.307A4.49 4.49 0 0 1 12 21.75a4.49 4.49 0 0 1-3.397-1.549 4.49 4.49 0 0 1-3.498-1.306 4.491 4.491 0 0 1-1.307-3.498A4.49 4.49 0 0 1 2.25 12c0-1.357.6-2.573 1.549-3.397a4.49 4.49 0 0 1 1.307-3.497 4.49 4.49 0 0 1 3.497-1.307Zm7.007 6.387a.75.75 0 1 0-1.22-.872l-3.236 4.53L9.53 12.22a.75.75 0 0 0-1.06 1.06l2.25 2.25a.75.75 0 0 0 1.14-.094l3.75-5.25Z" clip-rule="evenodd"/></svg>';

        return match ($type) {
            // Admin: purple verified badge.
            'admin'  => '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-[#16181d] text-white" title="Verified site administrator">'.$seal.'Admin</span>',
            // 100+ published posts: yellow badge (authors only).
            'top'    => '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-400 text-slate-900" title="Top author, 100+ published posts">'.$seal.'Top Author</span>',
            // 10+ published posts: green badge (authors only).
            default  => '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-[var(--brand)] text-white" title="Verified author, 10+ published posts">'.$seal.'Author</span>',
        };
    }

    /**
     * Is this admin currently browsing in "user mode"?
     *
     * Admins can temporarily switch their own session to user mode so they
     * can see the site + author dashboard exactly like a regular user would
     * (great for verifying changes). While active, /manage is blocked and the
     * frontend shows a "Switch back to Admin" button. Only the real admin
     * account can enter or leave this mode — a normal user NEVER gains admin
     * privileges from it.
     */
    public function browsingAsUser(): bool
    {
        if ($this->role !== 'admin') {
            return false;
        }
        try {
            return session('acting_role') === 'user';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * The role this user is currently ACTING as. Admins in user mode act as
     * 'author' (the normal logged-in experience); everyone else acts as their
     * stored role. Use this for UI decisions (which dashboard button to show).
     */
    public function actingRole(): string
    {
        if ($this->browsingAsUser()) {
            return 'author';
        }
        return $this->role ?? 'reader';
    }

    /**
     * Send the frontend password reset notification (Huvanti-branded email
     * that points to /reset-password/{token} instead of /manage).
     */
    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    /**
     * Published posts by this author (for the public profile page).
     */
    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->whereNotNull('published_at')->where('status', 'published')->orderByDesc('published_at');
    }

    /**
     * Followers of this user (people who follow this user).
     */
    public function followers()
    {
        return $this->belongsToMany(self::class, 'user_follows', 'followee_id', 'follower_id')->withTimestamps();
    }

    /**
     * Users that this user is following.
     */
    public function following()
    {
        return $this->belongsToMany(self::class, 'user_follows', 'follower_id', 'followee_id')->withTimestamps();
    }

    /**
     * The user's country as a human-readable name ("BD" → "Bangladesh"),
     * or null when no country was picked (or the code is unknown).
     * Used on the public author profile and the post byline.
     */
    public function countryName(): ?string
    {
        try {
            return \App\Support\Countries::name($this->country ?? null);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Country flag icon URL (flagcdn.com) for the stored ISO code, or null
     * when unset/invalid. Emoji flags are not used because Windows renders
     * them as plain letters instead of the flag.
     */
    public function countryFlagUrl(): ?string
    {
        try {
            return \App\Support\Countries::flagUrl($this->country ?? null);
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * The author's primary niche as a Category model, or null when not picked
     * or the category no longer exists / was disabled.
     * Shown on the public author profile next to the role title.
     */
    public function nicheCategory(): ?Category
    {
        if (! $this->niche) {
            return null;
        }
        try {
            return \App\Models\Category::query()
                ->where('slug', $this->niche)
                ->where('is_active', true)
                ->first();
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * The author's personal dashboard font (key into FontFamilies), or null
     * when unset or the key is no longer valid. Applies to THIS author's
     * dashboard only, never to the public site or other authors.
     */
    public function panelFontKey(): ?string
    {
        if (! $this->panel_font) {
            return null;
        }
        return array_key_exists($this->panel_font, \App\Support\FontFamilies::all())
            ? $this->panel_font
            : null;
    }

    /**
     * Resolve social_links JSON into a clean array of {platform, url, label}.
     */
    public function socialProfiles(): array
    {
        $raw = $this->social_links ?? [];
        if (!is_array($raw)) {
            $decoded = json_decode((string)$raw, true);
            $raw = is_array($decoded) ? $decoded : [];
        }
        $labels = [
            'x' => 'X', 'twitter' => 'Twitter', 'facebook' => 'Facebook',
            'linkedin' => 'LinkedIn', 'instagram' => 'Instagram', 'youtube' => 'YouTube',
            'pinterest' => 'Pinterest', 'whatsapp' => 'WhatsApp', 'telegram' => 'Telegram',
            'github' => 'GitHub', 'website' => 'Website', 'mastodon' => 'Mastodon', 'tiktok' => 'TikTok',
        ];
        $out = [];
        foreach ($raw as $platform => $url) {
            if (!is_string($url) || $url === '') {
                continue;
            }
            $out[] = [
                'platform' => $platform,
                'url' => $url,
                'label' => $labels[$platform] ?? ucfirst($platform),
            ];
        }
        return $out;
    }
}

