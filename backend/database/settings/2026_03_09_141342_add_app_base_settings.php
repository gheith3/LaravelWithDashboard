<?php

use App\Settings\AppBaseSettings;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $defaults = AppBaseSettings::defaults();

        // General settings
        $this->migrator->add('app_base_settings.website_url', $defaults['website_url']);
        $this->migrator->add('app_base_settings.default_language', $defaults['default_language']);
        $this->migrator->add('app_base_settings.supported_languages', $defaults['supported_languages']);
        $this->migrator->add('app_base_settings.name', $defaults['name']);
        $this->migrator->add('app_base_settings.version', $defaults['version']);
        $this->migrator->add('app_base_settings.about', $defaults['about']);
        $this->migrator->add('app_base_settings.copyright', $defaults['copyright']);
        $this->migrator->add('app_base_settings.terms_and_conditions', $defaults['terms_and_conditions']);
        $this->migrator->add('app_base_settings.privacy_policy', $defaults['privacy_policy']);
        $this->migrator->add('app_base_settings.contact', $defaults['contact']);
        $this->migrator->add('app_base_settings.social_media', $defaults['social_media']);
       }

    public function down(): void
    {
        // Remove all settings
        $this->migrator->delete('app_base_settings.website_url');
        $this->migrator->delete('app_base_settings.default_language');
        $this->migrator->delete('app_base_settings.supported_languages');
        $this->migrator->delete('app_base_settings.name');
        $this->migrator->delete('app_base_settings.version');
        $this->migrator->delete('app_base_settings.about');
        $this->migrator->delete('app_base_settings.copyright');
        $this->migrator->delete('app_base_settings.terms_and_conditions');
        $this->migrator->delete('app_base_settings.privacy_policy');
        $this->migrator->delete('app_base_settings.contact');
        $this->migrator->delete('app_base_settings.social_media');
    }
};
