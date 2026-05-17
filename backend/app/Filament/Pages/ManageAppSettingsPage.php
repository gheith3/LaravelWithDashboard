<?php

namespace App\Filament\Pages;

use App\Enums\AuditNotificationStaus;
use App\Settings\AppBaseSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Repeater;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Schemas\Components\Grid;

class ManageAppSettingsPage extends SettingsPage
{
    use HasPageShield;
    protected static string $settings = AppBaseSettings::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 100;

    public static function getRoutePath(\Filament\Panel $panel): string
    {
        return 'manage-app-settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('General Settings')
                    ->description('Basic application configuration')
                    ->icon('heroicon-o-globe-alt')
                    ->columns(2)
                    ->schema([
                        TextInput::make('website_url')
                            ->label('Website URL')
                            ->url()
                            ->required(),

                        TextInput::make('name')
                            ->label('App Name')
                            ->required(),

                        TextInput::make('version')
                            ->label('App Version')
                            ->required(),

                        Textarea::make('copyright')
                            ->label('Copyright Text')
                            ->rows(2)
                            ->columnSpanFull()
                            ->required(),

                        Textarea::make('about')
                            ->label('About App')
                            ->rows(3)
                            ->columnSpanFull()
                            ->required(),
                    ]),

                Section::make('Localization')
                    ->description('Language and currency settings')
                    ->icon('heroicon-o-language')
                    ->columns(2)
                    ->schema([
                        Select::make('default_language')
                            ->label('Default Language')
                            ->options([
                                'en' => 'English',
                                'ar' => 'Arabic',
                            ])
                            ->required(),

                        TagsInput::make('supported_languages')
                            ->label('Supported Languages')
                            ->placeholder('Add language code (e.g., en, ar)')
                            ->columnSpanFull()
                            ->required(),
                    ]),


                Section::make('Contact Information')
                    ->description('Support contact details')
                    ->icon('heroicon-o-phone')
                    ->collapsible()
                    ->schema([
                        KeyValue::make('contact')
                            ->label('Contact Details')
                            ->keyLabel('Type')
                            ->valueLabel('Value')
                            ->required(),

                        KeyValue::make('social_media')
                            ->label('Social Media Links')
                            ->keyLabel('Platform')
                            ->valueLabel('URL')
                            ->required(),
                    ]),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'App Settings';
    }

    public function getTitle(): string
    {
        return 'Manage App Settings';
    }

    public function getHeading(): string
    {
        return 'Application Settings';
    }

    public function getSubheading(): ?string
    {
        return 'Configure all application settings in one place';
    }
}
