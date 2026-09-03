<?php

namespace Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Shared\Traits\HasUuid;
use Shared\Models\User;
use Database\Factories\Modules\Marketing\ContactMessageFactory;

class ContactMessage extends Model
{
    use HasUuid, HasFactory;

    protected $table = 'marketing_contact_messages';

    protected $fillable = [
        'user_id', 'name', 'email', 'subject', 'message', 'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'replied_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function newFactory(): ContactMessageFactory
    {
        return ContactMessageFactory::new();
    }
}