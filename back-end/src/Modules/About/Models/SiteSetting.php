<?php

namespace Modules\About\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $table = 'about_site_settings';

    protected $fillable = [
        'call_us_phone',
        'call_us_emails',
        'visit_address',
        'social_links',
    ];

    protected function casts(): array
    {
        return [
            'call_us_emails' => 'array',
            'social_links' => 'json',
        ];
    }

    /**
     * Get the singleton settings row.
     */
    public static function singleton(): self
    {
        return self::firstOrFail();
    }
}