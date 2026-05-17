<?php

namespace App\Filament\Resources\MonitoredScheduledTask;

use App\Enums\AppUserRole;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use UnitEnum;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Support\Icons\Heroicon;
use Spatie\ScheduleMonitor\Models\MonitoredScheduledTask;

class MonitoredScheduledTaskResource extends Resource
{
    protected static ?string $model = MonitoredScheduledTask::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?string $label = 'Scheduled Task';

    protected static ?string $pluralLabel = 'Scheduled Tasks';

    protected static ?int $navigationSort = 200;


    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user->hasRole([AppUserRole::SuperAdmin->value]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('font-bold'),

                TextColumn::make('type')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'command' => 'primary',
                        'job' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('cron_expression')
                    ->label('Cron')
                    ->fontFamily('font-mono')
                    ->size('text-xs'),

                TextColumn::make('last_started_at')
                    ->label('Last Started')
                    ->dateTime('Y-m-d H:i:s')
                    ->since()
                    ->sortable(),

                TextColumn::make('last_finished_at')
                    ->label('Last Finished')
                    ->dateTime('Y-m-d H:i:s')
                    ->since()
                    ->sortable(),

                IconColumn::make('status')
                    ->label('Status')
                    ->icon(fn(MonitoredScheduledTask $record): Heroicon => match (true) {
                        $record->last_failed_at !== null && ($record->last_finished_at === null || $record->last_failed_at > $record->last_finished_at)
                        => Heroicon::StopCircle,
                        $record->last_started_at !== null && $record->last_finished_at === null
                        => Heroicon::PlayCircle,
                        default
                        => Heroicon::CheckCircle,
                    })
                    ->color(fn(MonitoredScheduledTask $record): string => match (true) {
                        $record->last_failed_at !== null && ($record->last_finished_at === null || $record->last_failed_at > $record->last_finished_at)
                        => 'danger',
                        $record->last_started_at !== null && $record->last_finished_at === null
                        => 'warning',
                        default
                        => 'success',
                    }),

                TextColumn::make('grace_time_in_minutes')
                    ->label('Grace Time')
                    ->suffix(' min')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('last_started_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->schema([
                Section::make('Task Information')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('name')
                            ->icon(Heroicon::OutlinedCommandLine)
                            ->columnSpanFull(),
                        TextEntry::make('type')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'command' => 'primary',
                                'job' => 'success',
                                default => 'gray',
                            }),
                        TextEntry::make('cron_expression')
                            ->label('Cron Expression')
                            ->icon(Heroicon::OutlinedCalendar),
                        TextEntry::make('timezone')
                            ->icon(Heroicon::OutlinedGlobeAlt),
                        TextEntry::make('grace_time_in_minutes')
                            ->label('Grace Time')
                            ->suffix(' minutes'),

                        TextEntry::make('last_started_at')
                            ->label('Last Started')
                            ->dateTime('Y-m-d H:i:s')
                            ->since()
                            ->icon(Heroicon::OutlinedPlay),
                        TextEntry::make('last_finished_at')
                            ->label('Last Finished')
                            ->dateTime('Y-m-d H:i:s')
                            ->since()
                            ->icon(Heroicon::OutlinedCheckCircle),
                        TextEntry::make('last_failed_at')
                            ->label('Last Failed')
                            ->dateTime('Y-m-d H:i:s')
                            ->since()
                            ->icon(Heroicon::OutlinedXCircle)
                            ->color('danger'),
                        TextEntry::make('last_skipped_at')
                            ->label('Last Skipped')
                            ->dateTime('Y-m-d H:i:s')
                            ->since()
                            ->icon(Heroicon::OutlinedForward),
                    ]),

                Section::make('Latest Log Output')
                    ->description('Last 200 lines from the log file')
                    ->collapsible()
                    ->schema([
                        TextEntry::make('log_content')
                            ->label('')
                            ->default(function (MonitoredScheduledTask $record): string {
                                return static::getLatestLogContent($record->name);
                            })
                            ->extraAttributes([
                                'class' => 'font-mono text-xs whitespace-pre-wrap bg-gray-950 text-gray-100 p-4 rounded-lg max-h-96 overflow-y-auto',
                            ])
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Get the latest log content for a task based on its name.
     */
    protected static function getLatestLogContent(string $taskName): string
    {
        // Map task names to log files based on console.php configuration
        $logFile = match (true) {
            str_contains($taskName, 'orders:expire-unpaid') => storage_path('logs/expire-orders.log'),
            str_contains($taskName, 'boarding-passes:expire') => storage_path('logs/expire-boarding-passes.log'),
            str_contains($taskName, 'carts:expire') => storage_path('logs/expire-carts.log'),
            str_contains($taskName, 'db:backup') => storage_path('logs/db-backup.log'),
            str_contains($taskName, 'items:import') => storage_path('logs/items-import.log'),
            default => null,
        };

        if (!$logFile || !file_exists($logFile)) {
            return 'No log file found for this task.';
        }

        // Read file and get last 200 lines
        $content = file_get_contents($logFile);

        if (empty($content)) {
            return 'Log file is empty.';
        }

        $lines = explode("\n", $content);
        $lines = array_filter($lines);
        $lastLines = array_slice($lines, -100);

        return implode("\n", $lastLines) ?: 'Log file is empty.';
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\LogItemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMonitoredScheduledTask::route('/'),
            'view' => Pages\ViewMonitoredScheduledTask::route('/{record}'),
        ];
    }
}
