<?php

namespace Modules\Taxonomy\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Modules\Taxonomy\CategoryFactory;

class Category extends Model
{
    use HasUuid, HasFactory;

    protected $table = 'content_categories';

    protected $fillable = ['name', 'slug'];

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if ($term) {
            return $query->where('name', 'like', "%{$term}%");
        }

        return $query;
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}