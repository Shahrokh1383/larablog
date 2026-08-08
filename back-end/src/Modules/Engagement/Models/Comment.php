<?php

namespace Modules\Engagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shared\Traits\HasUuid;
use Shared\Models\User;

class Comment extends Model
{
    use HasUuid;

    protected $table = 'engagement_comments';

    protected $fillable = [
        'post_id', 'user_id', 'parent_id', 'name', 'email', 
        'body', 'is_approved', 'read_at',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'read_at'     => 'datetime',
    ];

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('is_approved', true);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->approved()->latest();
    }
}