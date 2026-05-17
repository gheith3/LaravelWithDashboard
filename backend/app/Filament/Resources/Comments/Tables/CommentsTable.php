<?php

namespace App\Filament\Resources\Comments\Tables;

use App\Enums\CommentStatus;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CommentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('post.title')
                    ->label('Post')
                    ->searchable()
                    ->sortable()
                    ->limit(40)
                    ->weight('bold'),

                TextColumn::make('name')
                    ->label('Name')
                    ->searchable()
                    ->description(fn ($record) => $record->email),

                TextColumn::make('phone')
                    ->label('Phone')
                    ->icon('heroicon-o-phone')
                    ->iconColor('gray')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('content')
                    ->label('Comment')
                    ->limit(60)
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(CommentStatus::class)
                    ->native(false),

                SelectFilter::make('post')
                    ->label('Post')
                    ->relationship('post', 'title')
                    ->searchable()
                    ->preload()
                    ->native(false),

                TrashedFilter::make(),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make()->iconButton(),
                EditAction::make()->iconButton(),
                Action::make('accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->iconButton()
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status !== CommentStatus::Accepted)
                    ->action(fn ($record) => $record->update(['status' => CommentStatus::Accepted])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->striped()
            ->paginated([10, 25, 50]);
    }
}
