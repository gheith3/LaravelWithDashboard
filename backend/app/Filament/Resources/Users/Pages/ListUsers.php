<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\AppUserRole;
use App\Filament\Resources\Users\UserResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->icon('heroicon-o-plus'),
        ];
    }

    public function getTabs(): array
    {
        $tabs = [];
        $tabs['all'] = Tab::make('All')
            ->icon('heroicon-o-users');
        foreach (AppUserRole::cases() as $role) {
            $tabs[$role->value] = Tab::make($role->getLabel())
                ->icon('heroicon-o-building-office')
                ->modifyQueryUsing(fn (Builder $query) => $query->role($role->value));
        }

        return $tabs;
    }
}
