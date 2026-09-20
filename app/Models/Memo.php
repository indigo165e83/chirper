<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Memo extends Model
{
    use HasFactory;
    
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
