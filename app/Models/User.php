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

