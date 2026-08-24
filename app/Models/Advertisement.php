<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    protected $fillable = ['title','position','code','image','link','is_active','sort_order','starts_at','ends_at','clicks','impressions'];
    protected $casts = ['is_active'=>'boolean','starts_at'=>'datetime','ends_at'=>'datetime'];

    /**
     * "Active" means:
     *   1. is_active = true
     *   2. starts_at is null OR in the past
     *   3. ends_at is null OR in the future
     *   4. ordered by sort_order ASC, then by id ASC for tie-breaking.
     * The frontend uses ->first() to grab the highest-priority ad per slot.
     */
    public function scopeActive($q)
    {
        return $q->where('is_active', true)
            ->where(function($qq){
                $qq->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function($qq){
                $qq->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            })
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function scopePosition($q, $pos)
    {
        return $q->where('position', $pos);
    }
}

