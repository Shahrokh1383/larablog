<?php

namespace Modules\Profile\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Models\User;

class Profile extends Model
{
    use HasUuids;

    protected $fillable = [
        'user_id',
        'avatar',
        'bio',
        'expertise',
        'years_of_experience',
        'social_links',
    ];

    protected function casts(): array
    {
        return [
            'social_links'        => 'array',
            'years_of_experience' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}