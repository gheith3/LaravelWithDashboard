<?php

namespace App\Filament\Resources\MonitoredScheduledTask\Pages;

use App\Filament\Resources\MonitoredScheduledTask\MonitoredScheduledTaskResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class ListMonitoredScheduledTask extends ListRecords
{
    protected static string $resource = MonitoredScheduledTaskResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('sync')
                ->label('Sync Schedule')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('primary')
                ->requiresConfirmation()
                ->action(function () {
                    Artisan::call('schedule-monitor:sync');
                    Notification::make()
                        ->success()
                        ->body('Schedule synced successfully!');
                }),
        ];
    }
}
