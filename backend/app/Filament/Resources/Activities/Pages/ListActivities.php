<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListActivities extends ListRecords
{
    protected static string $resource = ActivityResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All')
                ->icon('heroicon-o-rectangle-stack'),

            'today' => Tab::make('Today')
                ->icon('heroicon-o-calendar')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereDate('created_at', today()))
                ->badge(fn () => $this->getModel()::whereDate('created_at', today())->count())
                ->badgeColor('info'),

            'created' => Tab::make('Created')
                ->icon('heroicon-o-plus-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event', 'created'))
                ->badge(fn () => $this->getModel()::where('event', 'created')->count())
                ->badgeColor('success'),

            'updated' => Tab::make('Updated')
                ->icon('heroicon-o-pencil-square')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event', 'updated'))
                ->badge(fn () => $this->getModel()::where('event', 'updated')->count())
                ->badgeColor('info'),

            'deleted' => Tab::make('Deleted')
                ->icon('heroicon-o-trash')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('event', 'deleted'))
                ->badge(fn () => $this->getModel()::where('event', 'deleted')->count())
                ->badgeColor('danger'),
        ];
    }
}
