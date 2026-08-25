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

    /** Flush ALL cached settings — call after bulk updates. */
    public static function flushAllCache()
    {
        // Forget individual setting keys (common pattern: "setting_xxx")
        try {
            $store = Cache::getStore();
            if (method_exists($store, 'getDirectory')) {
                $dir = $store->getDirectory();
                foreach (glob($dir . '/setting_*') as $f) {
                    @unlink($f);
                }
            }
        } catch (\Throwable $e) {
            // Fallback: just clear the entire cache
            try { Cache::flush(); } catch (\Throwable $e2) {}
        }
    }

    protected static function booted()
    {
        static::saved(fn($m)=> Cache::forget("setting_{$m->key}"));
        static::deleted(fn($m)=> Cache::forget("setting_{$m->key}"));
    }
}
