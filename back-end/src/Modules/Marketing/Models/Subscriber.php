<?php

namespace Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Shared\Traits\HasUuid;
use Database\Factories\Modules\Marketing\SubscriberFactory;

class Subscriber extends Model
{
    use HasUuid, HasFactory;

    protected $table = 'marketing_subscribers';

    protected $fillable = ['email', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function newFactory(): SubscriberFactory
    {
        return SubscriberFactory::new();
    }
}