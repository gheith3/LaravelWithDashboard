<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AppUserRole;
use App\Enums\FileStoragePath;
use Filament\Actions\Action as ActionsAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Personal Information')
                    ->description('User\'s basic information and contact details')
                    ->icon('heroicon-o-user')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('John Doe'),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('john@example.com'),

                            TextInput::make('country_code')
                            ->label('Country Code')
                            ->maxLength(4)
                            ->placeholder('968'),


                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+968 9XXX XXXX'),

                        FileUpload::make('avatar_url')
                            ->label('Profile Photo')
                            ->image()
                            ->avatar()
                            ->circleCropper()
                            ->imageEditor()
                            ->directory(FileStoragePath::UserAvatar->value)
                            // ->activeUrl(true)
                            ->columnSpanFull(),
                    ]),

                Section::make('Security')
                    ->description('Password and authentication settings')
                    ->icon('heroicon-o-lock-closed')
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn ($state) => $state ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->minLength(8)
                            ->same('password_confirmation')
                            ->suffixAction(
                                ActionsAction::make('generatePassword')
                                    ->icon('heroicon-o-sparkles')
                                    ->action(function ($set) {
                                        $password = Str::password(12);
                                        $set('password', $password);
                                        $set('password_confirmation', $password);
                                    })
                            ),

                        TextInput::make('password_confirmation')
                            ->label('Confirm Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->native(false)
                            ->displayFormat('M d, Y H:i')
                            ->columnSpanFull(),
                    ]),

                Section::make('User Type')
                    ->description('Define user role and type')
                    ->icon('heroicon-o-identification')
                    ->schema([

                        Select::make('roles')
                            ->label('Roles')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->options(function () {
                                return Role::query()
                                    ->when(! auth()->user()->hasRole(AppUserRole::SuperAdmin->value), function ($query) {
                                        $query->where('name', '!=', AppUserRole::SuperAdmin->value);
                                    })->pluck('name', 'id');
                            })
                            ->native(false),
                    ]),

                Section::make('Status')
                    ->description('Account status settings')
                    ->icon('heroicon-o-signal')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive users cannot log in')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->onColor('success')
                            ->offColor('danger'),
                    ]),
            ]);
    }
}
