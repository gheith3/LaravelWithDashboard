<?php

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Resources\Posts\PostResource;
use App\Filament\Traits\ViewPageWithTabs;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPost extends ViewRecord
{
    use ViewPageWithTabs;
    protected static string $resource = PostResource::class;
    protected static string $mainTabTitle = "Post Details";


    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
