<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Post Details')
                    ->icon('heroicon-o-document-text')
                    ->columns(3)
                    ->schema([
                         TextEntry::make('slug')
                            ->label('Slug')
                            ->icon('heroicon-o-link')
                            ->fontFamily('mono')
                            ->copyable()
                            ->copyMessage('Slug copied!'),

                        TextEntry::make('title')
                            ->label('Title'),


                            TextEntry::make('creator.name')
                            ->label('Creator'),

                        

                        ImageEntry::make('header_image')
                            ->label('Header Image')
                            ->imageHeight(200)
                            ->columnSpanFull()
                            ->visible(fn (Post $record): bool => filled($record->header_image)),
                    ]),

                Section::make('Content')
                    ->icon('heroicon-o-bars-3-bottom-left')
                    ->schema([
                        TextEntry::make('content')
                            ->label('')
                            ->html()
                            ->columnSpanFull(),
                    ]),

                Section::make('Tags')
                    ->icon('heroicon-o-tag')
                    ->schema([
                        TextEntry::make('tags.name')
                            ->label('Tags')
                            ->badge()
                            ->placeholder('No tags assigned'),
                    ]),

                Section::make('Status')
                    ->icon('heroicon-o-signal')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),
                            
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean()
                            ->trueIcon('heroicon-o-check-circle')
                            ->falseIcon('heroicon-o-x-circle')
                            ->trueColor('success')
                            ->falseColor('danger'),
                    ]),

                Section::make('Timestamps')
                    ->icon('heroicon-o-clock')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Last Updated')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-pencil-square'),

                        TextEntry::make('deleted_at')
                            ->label('Deleted At')
                            ->dateTime('F j, Y \a\t g:i A')
                            ->icon('heroicon-o-trash')
                            ->visible(fn (Post $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
