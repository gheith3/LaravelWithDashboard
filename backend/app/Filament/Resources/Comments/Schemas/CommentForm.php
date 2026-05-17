<?php

namespace App\Filament\Resources\Comments\Schemas;

use App\Enums\CommentStatus;
use App\Models\Post;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CommentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Post')
                    ->description('The post this comment belongs to')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Select::make('post_id')
                            ->label('Post')
                            ->relationship('post', 'title')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->native(false),
                    ]),

                Section::make('Commenter Details')
                    ->description('Information about the comment author')
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
                            ->maxLength(255)
                            ->placeholder('john@example.com'),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->maxLength(20)
                            ->placeholder('+968 9XXX XXXX'),
                    ]),

                Section::make('Comment')
                    ->description('Comment content and moderation status')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->schema([
                        Textarea::make('content')
                            ->label('Comment')
                            ->required()
                            ->rows(5)
                            ->maxLength(2000)
                            ->columnSpanFull(),

                        Select::make('status')
                            ->label('Status')
                            ->options(CommentStatus::class)
                            ->default(CommentStatus::Review)
                            ->required()
                            ->native(false),
                    ]),
            ]);
    }
}
