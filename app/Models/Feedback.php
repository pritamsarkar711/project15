<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedbacks';

    protected $fillable = [
        'user_id',
        'overall_experience',
        'profile_ease',
        'publishing_ease',
        'bug_report',
        'what_you_like',
        'what_to_improve',
        'feature_request',
        'additional_comment',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
