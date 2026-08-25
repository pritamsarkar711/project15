<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key','value','type','group'];

    /**
     * Cache TTL in seconds (30s) — short enough that admin setting changes
     * propagate quickly, long enough to avoid hammering the DB on every request.
     * The old `rememberForever` caused settings to stick permanently on some
     * shared-hosting setups where Cache::forget() was unreliable.
     */
    private static int $cacheTtl = 30;

    public static function get($key, $default = null)
    {
        return Cache::remember("setting_{$key}", self::$cacheTtl, function() use ($key, $default) {
            $s = static::where('key',$key)->first();
            return $s ? $s->value : $default;
        });
    }

    public static function set($key, $value, $type='text', $group='general')
    {
        Cache::forget("setting_{$key}");
        return static::updateOrCreate(['key'=>$key], ['value'=>$value,'type'=>$type,'group'=>$group]);
    }

    /**
     * Flush ALL cached settings — call after bulk updates.
     *
     * The previous implementation tried to glob cache files on disk, which is a
     * silent no-op on the database cache driver (the default here) AND on the
     * file driver (keys are stored as sha1 hashes, so "setting_*" never matches).
     * Result: stale settings for up to 30s — or longer when OPcache served old
     * compiled views. This version forgets every known key from the DB and then
     * flushes the whole cache store as a safety net (the app only caches
     * settings, so a full flush is safe).
     */
    public static function flushAllCache()
    {
        // 1. Forget each known setting key (works on every cache driver).
        try {
            foreach (static::query()->pluck('key') as $key) {
                Cache::forget("setting_{$key}");
            }
        } catch (\Throwable $e) {
            // DB unreachable (e.g. during install) — per-key forget in set()
            // has already run for anything modified this request.
        }

        // 2. Safety net: drop anything else the current driver may hold.
        try { Cache::flush(); } catch (\Throwable $e) {}
    }

    protected static function booted()
    {
        static::saved(fn($m)=> Cache::forget("setting_{$m->key}"));
        static::deleted(fn($m)=> Cache::forget("setting_{$m->key}"));
    }
}
