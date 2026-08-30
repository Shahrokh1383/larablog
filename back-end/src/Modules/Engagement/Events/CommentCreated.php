<?php

namespace Modules\Engagement\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Engagement\Models\Comment;

class CommentCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Comment $comment,
        public ?string $originalParentId = null
    ) {}
}