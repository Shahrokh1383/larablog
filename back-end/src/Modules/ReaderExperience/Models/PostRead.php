<?php

namespace Modules\ReaderExperience\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Traits\HasUuid;
use Shared\Models\User;
use Modules\Content\Models\Post;

class PostRead extends Model
{
    use HasUuid;

    protected $table = 'reader_post_reads';

    public $timestamps = false; // We only use read_at

    protected $fillable = ['user_id', 'post_id', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Internal implementation detail.
     * NOTE: Do not access this relationship from outside the ReaderExperience module.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}