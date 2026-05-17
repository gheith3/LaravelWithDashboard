<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ActivityInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Causer Information')
                    ->icon('heroicon-o-user')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('causer_type')
                            ->label('Causer Type')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : 'System')
                            ->icon('heroicon-o-user-group')
                            ->placeholder('System'),

                        TextEntry::make('causer_id')
                            ->label('Causer ID')
                            ->fontFamily('mono')
                            ->icon('heroicon-o-hashtag')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('causer.name')
                            ->label('Causer Name')
                            ->icon('heroicon-o-user')
                            ->placeholder('System')
                            ->weight('bold'),

                        TextEntry::make('causer.email')
                            ->label('Causer Email')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('—')
                            ->copyable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Subject History Timeline')
                    ->icon('heroicon-o-clock')
                    ->collapsible()
                    ->collapsed(false)
                    ->schema([
                        ViewEntry::make('subject_history')
                            ->view('filament.infolists.components.activity-timeline')
                            ->columnSpanFull(),
                    ])
                    ->visible(fn ($record): bool => ! empty($record->subject_type) && ! empty($record->subject_id)),

                Section::make('Activity Details')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Activity ID')
                            ->icon('heroicon-o-finger-print')
                            ->fontFamily('mono')
                            ->size('sm')
                            ->copyable(),

                        TextEntry::make('log_name')
                            ->label('Log Name')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'default' => 'gray',
                                'auth' => 'info',
                                'system' => 'warning',
                                default => 'primary',
                            }),

                        TextEntry::make('event')
                            ->label('Event')
                            ->badge()
                            ->color(fn (?string $state): string => match ($state) {
                                'created' => 'success',
                                'updated' => 'info',
                                'deleted' => 'danger',
                                'restored' => 'warning',
                                'login' => 'success',
                                'logout' => 'gray',
                                default => 'primary',
                            })
                            ->formatStateUsing(fn (?string $state): string => $state ? Str::title($state) : '—'),

                        TextEntry::make('subject_type')
                            ->label('Subject Type')
                            ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '—')
                            ->icon('heroicon-o-cube')
                            ->placeholder('—'),

                        TextEntry::make('subject_id')
                            ->label('Subject ID')
                            ->fontFamily('mono')
                            ->icon('heroicon-o-hashtag')
                            ->placeholder('—')
                            ->copyable(),

                        TextEntry::make('subject_name')
                            ->label('Subject Name')
                            ->getStateUsing(function ($record): ?string {
                                $subject = $record->subject;

                                if (! $subject) {
                                    return null;
                                }

                                return $subject->name ?? $subject->title ?? $subject->label ?? "#{$subject->id}";
                            })
                            ->icon('heroicon-o-identification')
                            ->placeholder('—'),
                    ]),

                Section::make('Additional Properties')
                    ->icon('heroicon-o-code-bracket')
                    ->schema([
                        TextEntry::make('all_properties')
                            ->label('')
                            ->getStateUsing(fn ($record): ?string => ! empty($record->properties)
                                ? json_encode($record->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                                : null)
                            ->fontFamily('mono')
                            ->size('sm')
                            ->placeholder('No additional properties')
                            ->copyable(),
                    ])
                    ->visible(fn ($record): bool => ! empty($record->properties) && empty($record->properties['old']) && empty($record->properties['attributes']))
                    ->collapsed(),

                Section::make('Batch Information')
                    ->icon('heroicon-o-rectangle-group')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('batch_uuid')
                            ->label('Batch UUID')
                            ->fontFamily('mono')
                            ->size('sm')
                            ->icon('heroicon-o-finger-print')
                            ->copyable(),

                        TextEntry::make('batch_count')
                            ->label('Activities in Batch')
                            ->getStateUsing(function ($record): int {
                                if (! $record->batch_uuid) {
                                    return 0;
                                }

                                return \Spatie\Activitylog\Models\Activity::where('batch_uuid', $record->batch_uuid)->count();
                            })
                            ->icon('heroicon-o-queue-list')
                            ->badge()
                            ->color('info'),
                    ])
                    ->visible(fn ($record): bool => ! empty($record->batch_uuid)),

                Section::make('Timestamps')
                    ->icon('heroicon-o-clock')
                    ->columns(2)
                    ->collapsed()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime('F j, Y \a\t g:i:s A')
                            ->icon('heroicon-o-calendar'),

                        TextEntry::make('updated_at')
                            ->label('Updated At')
                            ->dateTime('F j, Y \a\t g:i:s A')
                            ->icon('heroicon-o-pencil-square'),
                    ]),

            ]);
    }
}
