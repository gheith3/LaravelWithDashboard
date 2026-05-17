<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Enums\FileStoragePath;
use App\Enums\PostStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Post Content')
                    ->description('Main content and media for the post')
                    ->icon('heroicon-o-document-text')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter post title')
                            ->live(debounce: 500)
                            ->afterStateUpdated(fn ($set, ?string $state) => $set('slug', Str::slug($state ?? ''))),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('auto-generated-from-title')
                            ->unique(ignoreRecord: true),

                        FileUpload::make('header_image')
                            ->label('Header Image')
                            ->image()
                            ->imageEditor()
                            ->directory(FileStoragePath::PostHeaderImage->value)
                            ->columnSpanFull(),

                        RichEditor::make('content')
                            ->label('Content')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Section::make('Classification')
                    ->description('Post status and associated tags')
                    ->icon('heroicon-o-tag')
                    ->columns(2)
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options(PostStatus::class)
                            ->default(PostStatus::Draft)
                            ->required()
                            ->native(false),

                        Select::make('tags')
                            ->label('Tags')
                            ->multiple()
                            ->relationship('tags', 'name')
                            ->preload()
                            ->native(false),
                    ]),

                Section::make('Visibility')
                    ->description('Control post visibility')
                    ->icon('heroicon-o-eye')
                    ->schema([
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->helperText('Inactive posts are hidden from the public')
                            ->onIcon('heroicon-o-check')
                            ->offIcon('heroicon-o-x-mark')
                            ->onColor('success')
                            ->offColor('danger'),
                    ]),
            ]);
    }
}
