<?php

namespace Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Shared\Traits\HasUuid;

class Subscriber extends Model
{
    use HasUuid;

    protected $table = 'marketing_subscribers';

    protected $fillable = ['email', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}