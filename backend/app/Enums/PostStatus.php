<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum PostStatus: string implements HasLabel, HasColor, HasDescription
{
    case Draft = 'draft';
    case Published = 'published';
    case Archived = 'archived';

    public function getLabel(): string
    {
        return match ($this) {
            self::Draft     => 'Draft',
            self::Published => 'Published',
            self::Archived  => 'Archived',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Draft     => 'Post is saved but not yet visible to the public',
            self::Published => 'Post is live and visible to the public',
            self::Archived  => 'Post is hidden and no longer active',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Draft     => 'warning',
            self::Published => 'success',
            self::Archived  => 'gray',
        };
    }
}
