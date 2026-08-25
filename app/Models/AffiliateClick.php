<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateClick extends Model
{
    protected $fillable = ['post_id', 'user_id', 'url_hash', 'ip_hash'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
