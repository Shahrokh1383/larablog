<?php

namespace Modules\ReaderExperience\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Traits\HasUuid;
use Shared\Models\User;

class SavedPost extends Model
{
    use HasUuid;

    protected $table = 'reader_saved_posts';
    public $timestamps = false; 
    protected $fillable = ['user_id', 'post_id', 'saved_at'];
    protected $casts = ['saved_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
}