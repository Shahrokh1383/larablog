<?php

namespace Modules\Engagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Shared\Traits\HasUuid;
use Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Modules\Engagement\CommentFactory;

class Comment extends Model
{
    use HasUuid, HasFactory;

    protected $table = 'engagement_comments';

    protected $fillable = [
        'post_id', 'user_id', 'parent_id', 'name', 'email', 
        'body', 'is_approved',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
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

    protected static function newFactory(): CommentFactory
    {
        return CommentFactory::new();
    }
}