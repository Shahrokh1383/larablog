<?php

namespace Modules\About\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Database\Factories\Modules\About\SiteSettingFactory;

class SiteSetting extends Model
{
    use HasFactory;

    protected $table = 'about_site_settings';

    protected $fillable = [
        'call_us_phone',
        'call_us_emails',
        'visit_address',
        'social_links',
        'story_image',
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

    protected static function newFactory(): SiteSettingFactory
    {
        return SiteSettingFactory::new();
    }
}