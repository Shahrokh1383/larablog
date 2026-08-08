<?php

namespace Modules\ReaderExperience\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Traits\HasUuid;
use Shared\Models\User;
use Modules\Content\Models\Post;

class SavedPost extends Model
{
    use HasUuid;

    protected $table = 'reader_saved_posts';

    public $timestamps = false; // We only use saved_at

    protected $fillable = ['user_id', 'post_id', 'saved_at'];

    protected $casts = [
        'saved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Internal implementation detail.
     * NOTE: Do not access this relationship from outside the ReaderExperience module.
     * Cross-module data retrieval must go through Content Services.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}