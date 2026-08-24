<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title','slug','excerpt','content','featured_image','category_id','user_id',
        'author_name','author_bio','author_avatar','reading_time','status','published_at','scheduled_at',
        'meta_title','meta_description','meta_keywords','views','is_featured','allow_comments'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'deleted_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function faqs()
    {
        return $this->hasMany(Faq::class)->orderBy('sort_order');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function approvedComments()
    {
        return $this->hasMany(Comment::class)->where('status','approved')->latest();
    }

    /**
     * Frontend-visible posts: published, not soft-deleted, not scheduled for the future.
     * A post is live when status=published, (published_at is null or <= now)
     * and (scheduled_at is null or <= now).
     */
    public function scopePublished($query)
    {
        return $query->where('status','published')
            ->where(function ($q) {
                $q->whereNull('published_at')->orWhere('published_at','<=',now());
            })
            ->where(function ($q) {
                $q->whereNull('scheduled_at')->orWhere('scheduled_at','<=',now());
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    protected static function booted()
    {
        static::creating(function ($post) {
            if (empty($post->slug)) $post->slug = Str::slug($post->title);
            if (empty($post->reading_time)) {
                $post->reading_time = max(1, ceil(str_word_count(strip_tags($post->content)) / 200));
            }
            if ($post->status === 'published' && empty($post->published_at) && !self::isFutureScheduled($post)) {
                $post->published_at = now();
            }
        });
        static::updating(function ($post) {
            if ($post->isDirty('content')) {
                $post->reading_time = max(1, ceil(str_word_count(strip_tags($post->content)) / 200));
            }
            if ($post->isDirty('status') && $post->status === 'published' && empty($post->published_at) && !self::isFutureScheduled($post)) {
                $post->published_at = now();
            }
        });
    }

    protected static function isFutureScheduled($post)
    {
        return $post->scheduled_at !== null && \Illuminate\Support\Carbon::parse($post->scheduled_at)->isFuture();
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function getTableOfContentsAttribute()
    {
        preg_match_all('/<h[2-3][^>]*>(.*?)<\/h[2-3]>/i', $this->content, $matches);
        $toc = [];
        foreach ($matches[1] as $idx => $title) {
            $id = Str::slug(strip_tags($title)).'-'.$idx;
            $toc[] = ['id'=>$id,'title'=>strip_tags($title)];
        }
        return $toc;
    }
}
