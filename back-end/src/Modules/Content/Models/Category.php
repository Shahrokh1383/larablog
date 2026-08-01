<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Modules\Content\CategoryFactory;

class Category extends Model
{
    use HasUuid, HasFactory;

    protected $table = 'content_categories';

    protected $fillable = ['name', 'slug'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    protected static function newFactory(): CategoryFactory
    {
        return CategoryFactory::new();
    }
}