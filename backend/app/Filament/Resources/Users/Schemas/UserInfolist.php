<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Personal Information')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Full Name')
                            ->size('lg')
                            ->weight('bold'),

                        TextEntry::make('email')
                            ->label('Email Address')
                            ->icon('heroicon-o-envelope')
                            ->copyable()
                            ->copyMessage('Email copied!'),

                        TextEntry::make('phone')
                            ->label('Phone Number')
                            ->icon('heroicon-o-phone')
                            ->placeholder('Not provided')
                            ->copyable(),

                        TextEntry::make('id')
                            ->label('User ID')
                            ->icon('heroicon-o-finger-print')
                            ->fontFamily('mono')
                            ->size('sm')
                            ->copyable(),
                    ]),

                Section::make('Role & Access')
                    ->icon('heroicon-o-shield-check')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('roles.name')
                            ->label('User Type')
                            ->badge(),
                    ]),

                Section::make('Activity')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Account Created')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-pencil-square'),

                        TextEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-check-badge')
                            ->placeholder('Not verified'),
                    ]),
                Section::make('Profile')
                    ->schema([
                        ImageEntry::make('avatar_url')
                            ->label('')
                            ->circular()
                            ->size(150)
                            ->defaultImageUrl(fn ($record) => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&color=7F9CF5&background=EBF4FF&size=150'),
                    ]),

                Section::make('Status')
                    ->columns(3)
                    ->schema([
                        IconEntry::make('is_active')
                            ->label('Account Status')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),

                        IconEntry::make('email_verified_at')
                            ->label('Email Verified')
                            ->boolean()
                            ->getStateUsing(fn ($record) => $record->email_verified_at !== null)
                            ->trueIcon('heroicon-o-check-badge')
                            ->falseIcon('heroicon-o-x-mark')
                            ->trueColor('success')
                            ->falseColor('warning'),
                    ]),
            ]);
    }
}
