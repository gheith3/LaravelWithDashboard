<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;

enum AppUserRole: string implements HasLabel, HasColor, HasDescription
{
    case SuperAdmin = 'super_admin';
    case User = 'user';

    public function getLabel(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::User => 'User',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Full access to all platform features and settings',
            self::User => 'Standard user with limited access',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'danger',
            self::User => 'primary',
        };
    }


    public function canAccessAdmin(): bool
    {
        return match($this){
            self::SuperAdmin => true,
            default => false,
        };
    }

    public static function DahboardRoles(): array
    {
        return [
            self::SuperAdmin->value,
            // self::User->value,
        ];
    }
}
