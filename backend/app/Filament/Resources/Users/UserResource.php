<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users;

use App\Enums\AppUserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Filament\Resources\Users\Pages\ViewUser;
use App\Filament\Resources\Users\Schemas\UserForm;
use App\Filament\Resources\Users\Schemas\UserInfolist;
use App\Filament\Resources\Users\Tables\UsersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/**
 * User Resource for managing platform users.
 */
class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Users Management';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'full_name';

    /**
     * Get the navigation badge count.
     */
    public static function getNavigationBadge(): ?string
    {
        if (auth()->user()->hasRole(AppUserRole::SuperAdmin->value)) {
            return (string) static::getModel()::count();
        }

        return (string) parent::getEloquentQuery()
            ->where('account_id', auth()->user()->account_id)->count();
    }

    /**
     * Get the navigation badge color.
     */
    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'primary';
    }

    /**
     * Configure the form schema.
     */
    public static function form(Schema $schema): Schema
    {
        return UserForm::configure($schema);
    }

    /**
     * Configure the infolist schema.
     */
    public static function infolist(Schema $schema): Schema
    {
        return UserInfolist::configure($schema);
    }

    /**
     * Configure the table.
     */
    public static function table(Table $table): Table
    {
        return UsersTable::configure($table);
    }

    /**
     * Get the resource relations.
     *
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [];
    }

    /**
     * Get the resource pages.
     *
     * @return array<string, \Filament\Resources\Pages\PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'view' => ViewUser::route('/{record}'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }

    /**
     * Get the globally searchable attributes.
     *
     * @return array<string>
     */
    public static function getGloballySearchableAttributes(): array
    {
        return ['full_name', 'email', 'phone'];
    }

    /**
     * Get the model label.
     */
    public static function getModelLabel(): string
    {
        return __('User');
    }

    /**
     * Get the plural model label.
     */
    public static function getPluralModelLabel(): string
    {
        return __('Users');
    }

    /**
     * Get the navigation label.
     */
    public static function getNavigationLabel(): string
    {
        return __('Users');
    }

    public static function getEloquentQuery(): Builder
    {
        if (auth()->user()->hasRole(AppUserRole::SuperAdmin->value)) {
            return parent::getEloquentQuery();
        }

        return parent::getEloquentQuery()
            ->where('account_id', auth()->user()->account_id);
    }
}
