<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = [
        'app_name',
        'company_name',
        'vat_id',
        'qr_color',
        'default_website_url',
        'default_facebook_url',
        'default_instagram_url',
        'default_linkedin_url',
        'default_youtube_url',
    ];

    /**
     * Get the singleton settings row (creates one if missing).
     */
    public static function current(): self
    {
        return self::firstOrCreate(['id' => 1], [
            'app_name' => 'Reganta',
            'company_name' => null,
            'vat_id' => null,
            'qr_color' => '#000000',
            'default_website_url' => null,
            'default_facebook_url' => null,
            'default_instagram_url' => null,
            'default_linkedin_url' => null,
            'default_youtube_url' => null,
        ]);
    }
}
