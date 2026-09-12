<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Memo extends Model
{
    protected $fillable = [
        'title',
        'body',
        'is_draft',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    } 
}
