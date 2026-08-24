<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NavigationItem extends Model
{
    protected $fillable = ['label','url','position','sort_order','is_active','parent_id','icon'];
    protected $casts = ['is_active'=>'boolean'];
    public function children(){ return $this->hasMany(NavigationItem::class,'parent_id')->orderBy('sort_order'); }
    public function parent(){ return $this->belongsTo(NavigationItem::class,'parent_id'); }
    public function scopeActive($q){ return $q->where('is_active',true); }
    public function scopePosition($q,$pos){ return $q->where('position',$pos); }
}
