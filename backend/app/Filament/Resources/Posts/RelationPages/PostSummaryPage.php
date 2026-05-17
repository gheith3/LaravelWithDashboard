<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\RelationPages;

use gheith3\FilamentRelationPages\RelationPage;
use BackedEnum;
use Filament\Schemas\Schema;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;

class PostSummaryPage extends RelationPage
{
    protected static ?string $title = 'Post Summary';

    protected static string|BackedEnum|null $icon = null;

    // -------------------------------------------------------------------------
    // Filament schema content
    // Rendered in the view via: {{ $this->content }}
    // Use any Filament infolist / form components here.
    // -------------------------------------------------------------------------

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            //
        ]);
    }

    // -------------------------------------------------------------------------
    // Optional: dynamic badge on the tab
    // -------------------------------------------------------------------------

    // public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    // {
    //     return (string) $ownerRecord->items()->count();
    // }

    // -------------------------------------------------------------------------
    // Optional: hide this tab based on record / page
    // -------------------------------------------------------------------------

    // public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    // {
    //     return true;
    // }

    // -------------------------------------------------------------------------
    // Livewire render — return your Blade view
    // -------------------------------------------------------------------------

    public function render(): View
    {
        return view('filament.resources.posts.post-summary-page');
    }
}
