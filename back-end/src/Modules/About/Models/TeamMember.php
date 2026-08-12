<?php

namespace Modules\About\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Shared\Models\User;

class TeamMember extends Model
{
    use HasUuids;

    protected $table = 'about_team_members';

    protected $fillable = [
        'user_id',
        'display_name',
        'position',
        'bio',
        'photo',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}