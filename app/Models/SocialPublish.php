<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * One auto-post attempt of a Post to one social network.
 *
 * status:  pending   — queued, not tried yet
 *          success   — remote post created (external_url points at it)
 *          failed    — last attempt errored (see error)
 * A failed row is retried from the admin panel or via social:retry-pending.
 */
class SocialPublish extends Model
{
    public const NETWORKS = ['x', 'facebook', 'linkedin', 'instagram', 'telegram', 'pinterest'];

    protected $fillable = [
        'post_id', 'network', 'status', 'external_url', 'error', 'attempts', 'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public static function networkLabel(string $network): string
    {
        return match ($network) {
            'x'         => 'X (Twitter)',
            'facebook'  => 'Facebook Page',
            'linkedin'  => 'LinkedIn',
            'instagram' => 'Instagram',
            'telegram'  => 'Telegram',
            'pinterest' => 'Pinterest',
            default     => ucfirst($network),
        };
    }

    public static function brandColor(string $network): string
    {
        return match ($network) {
            'x'         => '#000000',
            'facebook'  => '#1877F2',
            'linkedin'  => '#0A66C2',
            'instagram' => '#E1306C',
            'telegram'  => '#229ED9',
            'pinterest' => '#E60023',
            default     => '#475569',
        };
    }
}
