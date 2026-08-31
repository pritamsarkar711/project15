<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name','slug','description','color','icon','sort_order','is_active'];

    protected $casts = ['is_active' => 'boolean'];

    /** Color no longer managed in admin — default to the brand green. */
    protected $attributes = [
        'color' => '#173A2A',
        'icon' => 'newspaper',
    ];

    /** Icon keys allowed in the admin icon picker (official Lucide outline names). */
    const ICONS = [
        // Tech / digital
        'cpu','smartphone','laptop','code','database','cloud','wifi','terminal','binary',
        // Lifestyle / general
        'newspaper','book','book-open','music','camera','film','tv','headphones','palette','pen-tool',
        // Money / business
        'banknote','wallet','credit-card','briefcase','chart-line','trending-up','coins','store','building','landmark',
        // Travel / places
        'plane','train','car','map','compass','globe','mountain','beach','tree-palm','tent',
        // Food / kitchen
        'utensils','coffee','wine','ice-cream','apple','carrot',
        // Health / fitness
        'heart-pulse','dumbbell','activity','brain','stethoscope','pill','cross','bone',
        // Learning / growth
        'graduation-cap','lightbulb','flask-conical','atom','microscope','telescope',
        // Mood / people (decorative "sparkles" style deliberately excluded)
        'sun','moon','smile','users','user','baby','hand-helping',
        // Misc ("shield" excluded - flashy/alert style icons are not allowed)
        'clock','calendar','gift','key','puzzle','fingerprint','flag','tag',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->where('status','published');
    }

    /**
     * Scope: categories that are visible on the public site.
     *
     * A category is "live" when:
     *   1. it is enabled in the admin panel (is_active), AND
     *   2. it has at least one PUBLISHED post (published now, not scheduled
     *      in the future, not soft-deleted).
     *
     * This keeps empty categories hidden from visitors until the first post
     * goes live under them — exactly how the menu/footer/homepage should
     * behave. The admin panel always shows every category regardless.
     */
    public function scopeLive($query)
    {
        return $query->where('is_active', true)
            ->whereHas('posts', fn ($q) => $q->published());
    }

    protected static function booted()
    {
        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
        static::updating(function ($category) {
            if (empty($category->slug) || $category->isDirty('name')) {
                // keep slug if manually set, otherwise regenerate only if empty
                if (empty($category->slug)) $category->slug = Str::slug($category->name);
            }
        });
    }
}
