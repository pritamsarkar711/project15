<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['post_id','name','email','content','status','parent_id','reply_depth','ip_address'];
    public function post(){ return $this->belongsTo(Post::class); }
    public function parent(){ return $this->belongsTo(Comment::class,'parent_id'); }
    public function replies(){ return $this->hasMany(Comment::class,'parent_id'); }
    public function scopeApproved($q){ return $q->where('status','approved'); }
}
