<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    protected $fillable = ['name','email','reason','subject','message','is_read','is_replied'];
    protected $casts = ['is_read'=>'boolean','is_replied'=>'boolean'];
}
