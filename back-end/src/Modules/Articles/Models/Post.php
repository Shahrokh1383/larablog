<?php

namespace Modules\Articles\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Shared\Traits\HasUuid;
use Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Modules\Articles\PostFactory;

class Post extends Model
{
    use HasUuid, HasFactory;

    protected $table = 'content_posts';

    protected $fillable = [
        'title', 'slug', 'body', 'excerpt', 'featured_image',
        'is_published', 'is_editors_pick', 'published_at', 'reading_time',
        'user_id', 'category_id', 'views',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_editors_pick' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function scopeByCategory(Builder $query, string $categoryId): Builder
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term) {
            return $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                  ->orWhere('excerpt', 'like', "%{$term}%");
            });
        }
        return $query;
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderBy('views', 'desc');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): PostFactory
    {
        return PostFactory::new();
    }
}