<?php

namespace Modules\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Shared\Traits\HasUuid;

class NewsletterSend extends Model
{
    use HasUuid;

    protected $table = 'marketing_newsletter_sends';

    public $timestamps = false;

    protected $fillable = ['subscriber_id', 'run_id', 'sent_at'];

    protected $casts = [
        'sent_at' => 'datetime',
    ];
}