<?php

namespace App\Settings;

use App\Enums\AuditNotificationStaus;
use App\Enums\Currency;
use Spatie\LaravelSettings\Settings;

class AppBaseSettings extends Settings
{
    // General App Settings
    public string $website_url;
    public string $default_language;
    public array $supported_languages;
    public string $name;
    public string $version;
    public string $about;
    public string $copyright;
    public string $terms_and_conditions;
    public string $privacy_policy;
            
    public ?array $contact = null;
    public ?array $social_media = null;

    public static function group(): string
    {
        return 'app_base_settings';
    }


    /**
     * Get the default values for all settings
     */
    public static function defaults(): array
    {
        return [
            // General
            'website_url' => 'https://navis.com',
            'default_language' => 'en',
            'supported_languages' => ['en'],
            'name' => 'NAVIS',
            'version' => '0.0.1',
            'about' => 'NAVIS is a premium beverage delivery service that brings the finest selection of beverages directly to your doorstep. Our mission is to provide you with the best quality beverages at competitive prices, with fast and reliable delivery service.',
            'copyright' => '© 2025 NAVIS. All rights reserved.',
            'terms_and_conditions' => 'app terms and conditions',
            'privacy_policy' => 'app privacy policy',
            
            'contact' => [
                'email' => 'info@oceanpearlworld.com',
                'phone' => '+968 9601 2777',
            ],
            'social_media' => [
                'facebook' => 'https://www.facebook.com/navis',
                'instagram' => 'https://www.instagram.com/navis',
                'twitter' => 'https://www.twitter.com/navis',
            ],
        ];
    }
}
