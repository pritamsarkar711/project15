<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    protected $fillable = ['title','slug','content','status','meta_title','meta_description'];
    public function getRouteKeyName(){ return 'slug'; }
    protected static function booted(){
        static::creating(fn($p)=> $p->slug = $p->slug ?: Str::slug($p->title));
    }
}
