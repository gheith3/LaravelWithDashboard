<?php

declare(strict_types=1);

namespace App\Filament\Resources\Activities;

use App\Filament\Resources\Activities\Pages\ListActivities;
use App\Filament\Resources\Activities\Pages\ViewActivity;
use App\Filament\Resources\Activities\Schemas\ActivityInfolist;
use App\Filament\Resources\Activities\Tables\ActivitiesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use UnitEnum;

/**
 * Activity Log Resource for viewing system activity logs.
 */
class ActivityResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'System';

    protected static ?int $navigationSort = 300;

    protected static ?string $recordTitleAttribute = 'description';

    /**
     * Get the navigation badge count.
     */
    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::whereDate('created_at', today())->count();
    }

    /**
     * Get the navigation badge color.
     */
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'info';
    }

    /**
     * Get the navigation badge tooltip.
     */
    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Activities today';
    }

    /**
     * Configure the infolist schema.
     */
    public static function infolist(Schema $schema): Schema
    {
        return ActivityInfolist::configure($schema);
    }

    /**
     * Configure the table.
     */
    public static function table(Table $table): Table
    {
        return ActivitiesTable::configure(
            $table->modifyQueryUsing(fn ($query) => $query->with(['causer', 'subject'])->latest())
        );
    }

    /**
     * Get the resource pages.
     *
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListActivities::route('/'),
            'view' => ViewActivity::route('/{record}'),
        ];
    }

    // /**
    //  * Get the globally searchable attributes.
    //  *
    //  * @return array<string>
    //  */
    // public static function getGloballySearchableAttributes(): array
    // {
    //     return ['description', 'log_name', 'event'];
    // }

    /**
     * Get the model label.
     */
    public static function getModelLabel(): string
    {
        return __('Activity Log');
    }

    /**
     * Get the plural model label.
     */
    public static function getPluralModelLabel(): string
    {
        return __('Activity Logs');
    }

    /**
     * Get the navigation label.
     */
    public static function getNavigationLabel(): string
    {
        return __('Activity Logs');
    }

    // /**
    //  * Determine whether the resource can be created.
    //  */
    // public static function canCreate(): bool
    // {
    //     return false;
    // }
}
