<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key','value','type','group'];
    
    public static function get($key, $default = null)
    {
        return Cache::rememberForever("setting_{$key}", function() use ($key, $default) {
            $s = static::where('key',$key)->first();
            return $s ? $s->value : $default;
        });
    }
    
    public static function set($key, $value, $type='text', $group='general')
    {
        Cache::forget("setting_{$key}");
        return static::updateOrCreate(['key'=>$key], ['value'=>$value,'type'=>$type,'group'=>$group]);
    }

    protected static function booted()
    {
        static::saved(fn($m)=> Cache::forget("setting_{$m->key}"));
        static::deleted(fn($m)=> Cache::forget("setting_{$m->key}"));
    }
}
