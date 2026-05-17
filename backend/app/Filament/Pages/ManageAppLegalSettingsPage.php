<?php

namespace App\Filament\Pages;

use App\Settings\AppBaseSettings;
use Filament\Forms\Components\RichEditor;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;

class ManageAppLegalSettingsPage extends SettingsPage
{
    use HasPageShield;
    protected static string $settings = AppBaseSettings::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string | \UnitEnum | null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 110;

    public static function getRoutePath(\Filament\Panel $panel): string
    {
        return 'manage-app-legal-settings';
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Legal Documents')
                    ->description('Terms and conditions, privacy policy')
                    ->icon('heroicon-o-document-text')
                    ->columns(1)
                    ->schema(
                        [
                            RichEditor::make('terms_and_conditions')
                                ->label('App Terms and Conditions')
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript'],
                                    ['h2', 'h3'],
                                    ['alignStart', 'alignCenter', 'alignEnd'],
                                    ['bulletList', 'orderedList'],
                                    ['undo', 'redo'],
                                ])
                                ->columnSpanFull()
                                ->required(),

                            RichEditor::make('privacy_policy')
                                ->label('App Privacy Policy')
                                ->toolbarButtons([
                                    ['bold', 'italic', 'underline', 'strike', 'subscript', 'superscript'],
                                    ['h2', 'h3'],
                                    ['alignStart', 'alignCenter', 'alignEnd'],
                                    ['bulletList', 'orderedList'],
                                    ['undo', 'redo'],
                                ])
                                ->columnSpanFull()
                                ->required(),
                        ]
                    ),
            ]);
    }

    public static function getNavigationLabel(): string
    {
        return 'App Legal Settings';
    }

    public function getTitle(): string
    {
        return 'Manage App Legal Settings';
    }

    public function getHeading(): string
    {
        return 'Application Legal Settings';
    }

    public function getSubheading(): ?string
    {
        return 'Configure application legal settings';
    }
}
