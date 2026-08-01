<?php

namespace Modules\Content\Models;

use Illuminate\Database\Eloquent\Model;
use Shared\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tag extends Model
{
    use HasUuid, HasFactory;

    protected $table = 'content_tags';

    protected $fillable = ['name', 'slug'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'content_post_tag', 'tag_id', 'post_id');
    }
}