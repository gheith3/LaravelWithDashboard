<?php

namespace App\Filament\Resources\MonitoredScheduledTask\RelationManagers;

use Filament\Actions\ViewAction;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Icons\Heroicon;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class LogItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'logItems';

    protected static ?string $title = 'Log Items';

    public static function getModelLabel(): string
    {
        return 'Log Item';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Log Items';
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->color(fn(string $state): string => match ($state) {
                        'starting' => 'primary',
                        'finished' => 'success',
                        'failed' => 'danger',
                        'skipped' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn(string $state): Heroicon => match ($state) {
                        'starting' => Heroicon::OutlinedPlay,
                        'finished' => Heroicon::OutlinedCheckCircle,
                        'failed' => Heroicon::OutlinedXCircle,
                        'skipped' => Heroicon::OutlinedForward,
                        default => Heroicon::OutlinedInformationCircle,
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i:s')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'starting' => 'Starting',
                        'finished' => 'Finished',
                        'failed' => 'Failed',
                        'skipped' => 'Skipped',
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Log Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('type')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'starting' => 'primary',
                                'finished' => 'success',
                                'failed' => 'danger',
                                'skipped' => 'warning',
                                default => 'gray',
                            })
                            ->icon(fn(string $state): Heroicon => match ($state) {
                                'starting' => Heroicon::OutlinedPlay,
                                'finished' => Heroicon::OutlinedCheckCircle,
                                'failed' => Heroicon::OutlinedXCircle,
                                'skipped' => Heroicon::OutlinedForward,
                                default => Heroicon::OutlinedInformationCircle,
                            }),
                        TextEntry::make('created_at')
                            ->label('Timestamp')
                            ->dateTime('Y-m-d H:i:s')
                            ->since()
                            ->icon(Heroicon::OutlinedClock),
                    ]),

                Section::make('Raw Data')
                    ->collapsible()
                    ->schema([
                        KeyValueEntry::make('meta')
                            ->label('Metadata (JSON)')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
