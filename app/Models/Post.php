<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasFactory;

    // Simpler than chasing fillable mismatches:
    protected $guarded = [];

    protected $casts = [
        'is_draft'     => 'boolean',
        'published_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Active: not draft & published_at <= now */
    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_draft', false)
                 ->whereNotNull('published_at')
                 ->where('published_at', '<=', now());
    }

    /** Scheduled: not draft & published_at > now */
    public function scopeScheduled(Builder $q): Builder
    {
        return $q->where('is_draft', false)
                 ->whereNotNull('published_at')
                 ->where('published_at', '>', now());
    }

    /** Draft: is_draft = true */
    public function scopeDraft(Builder $q): Builder
    {
        return $q->where('is_draft', true);
    }
}
