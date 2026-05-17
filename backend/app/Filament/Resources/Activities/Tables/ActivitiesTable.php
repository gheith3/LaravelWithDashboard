<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ActivitiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable()
                    ->icon('heroicon-o-calendar')
                    ->iconColor('gray'),

                // TextColumn::make('subject_type')
                //     ->label('Type')
                //     ->badge()
                //     ->searchable()
                //     ->sortable(),

                TextColumn::make('event')
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
                    ->formatStateUsing(fn (?string $state): string => $state ? Str::title($state) : '—')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(function (?string $state, $record): string {
                        if (! $state) {
                            return '—';
                        }

                        $modelName = class_basename($state);
                        $subjectId = $record->subject_id;

                        return "{$modelName} #{$subjectId}";
                    })
                    ->icon('heroicon-o-cube')
                    ->iconColor('gray')
                    ->searchable(['subject_type', 'subject_id'])
                    ->sortable(),

                TextColumn::make('causer.name')
                    ->label('Caused By')
                    ->default('System')
                    ->icon('heroicon-o-user')
                    ->iconColor('gray')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('batch_uuid')
                    ->label('Batch')
                    ->limit(8)
                    ->placeholder('—')
                    ->fontFamily('mono')
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('log_name')
                    ->label('Log Name')
                    ->options(fn () => \Spatie\Activitylog\Models\Activity::query()
                        ->distinct()
                        ->pluck('log_name', 'log_name')
                        ->toArray())
                    ->multiple()
                    ->preload(),

                SelectFilter::make('event')
                    ->label('Event')
                    ->options([
                        'created' => 'Created',
                        'updated' => 'Updated',
                        'deleted' => 'Deleted',
                        'restored' => 'Restored',
                        'login' => 'Login',
                        'logout' => 'Logout',
                    ])
                    ->multiple()
                    ->preload(),

                SelectFilter::make('subject_type')
                    ->label('Subject Type')
                    ->options(fn () => \Spatie\Activitylog\Models\Activity::query()
                        ->whereNotNull('subject_type')
                        ->distinct()
                        ->pluck('subject_type')
                        ->mapWithKeys(fn ($type) => [$type => class_basename($type)])
                        ->toArray())
                    ->multiple()
                    ->preload(),

                SelectFilter::make('causer_id')
                    ->label('Caused By')
                    ->options(function (): array {
                        return \Spatie\Activitylog\Models\Activity::query()
                            ->whereNotNull('causer_id')
                            ->whereNotNull('causer_type')
                            ->with('causer')
                            ->get()
                            ->pluck('causer')
                            ->filter()
                            ->unique('id')
                            ->mapWithKeys(fn ($causer) => [$causer->id => $causer->name ?? $causer->email ?? "#{$causer->id}"])
                            ->toArray();
                    })
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('from')
                            ->label('From'),
                        \Filament\Forms\Components\DatePicker::make('until')
                            ->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators['from'] = 'From '.\Carbon\Carbon::parse($data['from'])->format('M d, Y');
                        }

                        if ($data['until'] ?? null) {
                            $indicators['until'] = 'Until '.\Carbon\Carbon::parse($data['until'])->format('M d, Y');
                        }

                        return $indicators;
                    }),

                Filter::make('has_batch')
                    ->label('Has Batch')
                    ->toggle()
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('batch_uuid')),
            ], layout: FiltersLayout::AboveContentCollapsible)
            ->recordActions([
                ViewAction::make()
                    ->iconButton(),
            ])
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }
}
