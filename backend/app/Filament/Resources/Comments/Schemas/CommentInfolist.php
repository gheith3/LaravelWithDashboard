<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Models\Comment;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Post')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        TextEntry::make('post.title')
                            ->label('Post')
                            ->weight('bold')
                            ->icon('heroicon-o-document-text'),
                    ]),

                Section::make('Commenter Details')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Full Name')
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
                    ]),

                Section::make('Comment')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge(),

                        TextEntry::make('content')
                            ->label('Content')
                            ->columnSpanFull(),
                    ]),

                Section::make('Metadata')
                    ->icon('heroicon-o-finger-print')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Comment ID')
                            ->fontFamily('mono')
                            ->size('sm')
                            ->copyable(),

                        TextEntry::make('created_at')
                            ->label('Submitted')
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
                            ->visible(fn (Comment $record): bool => $record->trashed()),
                    ]),
            ]);
    }
}
