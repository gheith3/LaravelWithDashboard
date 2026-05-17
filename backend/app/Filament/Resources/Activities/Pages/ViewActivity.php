<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities\Pages;

use App\Filament\Resources\Activities\ActivityResource;
use App\Models\Application;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;

class ViewActivity extends ViewRecord
{
    protected static string $resource = ActivityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewSubject')
                ->label('View Subject')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->visible(fn (): bool => $this->record->subject !== null)
                ->url(function (): ?string {
                    $subject = $this->record->subject;

                    if (! $subject) {
                        return null;
                    }

                    $resourceClass = $this->getResourceForModel($subject);

                    if (! $resourceClass) {
                        return null;
                    }

                    return $resourceClass::getUrl('view', ['record' => $subject]);
                }),

            Action::make('viewBatch')
                ->label('View Batch')
                ->icon('heroicon-o-rectangle-group')
                ->color('info')
                ->visible(fn (): bool => ! empty($this->record->batch_uuid))
                ->url(fn (): string => ActivityResource::getUrl('index', [
                    'tableFilters' => [
                        'batch_uuid' => [
                            'value' => $this->record->batch_uuid,
                        ],
                    ],
                ])),
        ];
    }

    /**
     * Get the Filament resource class for a given model.
     */
    protected function getResourceForModel(object $model): ?string
    {
        $modelClass = get_class($model);

        $resourceMappings = [
            User::class => \App\Filament\Resources\Users\UserResource::class,
        ];

        return $resourceMappings[$modelClass] ?? null;
    }
}
