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

#[Fillable(['name', 'email', 'password', 'role', 'bio', 'avatar', 'two_factor_enabled', 'two_factor_secret', 'theme_preference', 'google2fa_secret', 'author_avatar_path', 'username', 'role_title', 'portfolio_url', 'social_links', 'is_verified'])]
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
     * Reusable badge markup used on author profiles and post author boxes.
     * Returns an empty string when the author has no badge.
     */
    public function badgeHtml(): string
    {
        $type = $this->badgeType();
        if ($type === null) {
            return '';
        }

        return match ($type) {
            'admin'  => '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-purple-600 text-white" title="Site administrator"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 1l2.23 4.55L17 6.36l-3.5 3.42.83 4.87L10 12.27l-4.33 2.38.83-4.87L3 6.36l4.77-1.81L10 1z" clip-rule="evenodd"/></svg>Admin</span>',
            'top'    => '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-400 text-slate-900" title="Top author, 100+ published posts"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 1l2.5 5.2 5.5.8-4 3.9.9 5.6L10 13.8 5.1 16.5 6 10.9 2 7l5.5-.8L10 1z"/></svg>Top Author</span>',
            default  => '<span class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-500 text-white" title="Author, 10+ published posts"><svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M13.6 2.7a2.2 2.2 0 1 1 3.1 3.1l-8 8-4.2 1.1 1.1-4.2 8-8z"/></svg>Author</span>',
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

