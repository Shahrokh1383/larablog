<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Shared\Traits\HasUuid;
use Shared\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Post extends Model
{
    use HasUuid, HasFactory;

    protected $table = 'content_posts';

    protected $fillable = [
        'title', 'slug', 'body', 'excerpt', 'featured_image',
        'is_published', 'published_at', 'reading_time',
        'user_id', 'category_id',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Local scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'content_post_tag', 'post_id', 'tag_id');
    }
}