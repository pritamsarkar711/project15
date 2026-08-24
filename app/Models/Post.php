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
        'meta_title','meta_description','meta_keywords','views','is_featured','allow_comments',
        'review_status','submitted_at','reviewed_at','reviewer_id','reviewer_note','is_affiliate'
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'deleted_at' => 'datetime',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'is_featured' => 'boolean',
        'allow_comments' => 'boolean',
        'is_affiliate' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The admin who last reviewed (approved / returned) this submission.
     */
    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Scope: posts awaiting admin review (review_status = pending_review).
     */
    public function scopePendingReview($query)
    {
        return $query->where('review_status', 'pending_review');
    }

    /**
     * Scope: posts authored by a given user (any review_status).
     */
    public function scopeByAuthor($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Has this author submitted a post in the last 24h? Enforces the
     * daily-1-post limit for non-admin authors.
     */
    public static function authorSubmittedRecently(int $userId): bool
    {
        return static::where('user_id', $userId)
            ->whereNotNull('submitted_at')
            ->where('submitted_at', '>=', now()->subDay())
            ->exists();
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
