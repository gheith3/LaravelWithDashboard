<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasDescription;
use Filament\Support\Contracts\HasLabel;

enum CommentStatus: string implements HasLabel, HasColor, HasDescription
{
    case Review = 'review';
    case Accepted = 'accepted';

    public function getLabel(): string
    {
        return match ($this) {
            self::Review   => 'Under Review',
            self::Accepted => 'Accepted',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::Review   => 'Comment is pending moderation',
            self::Accepted => 'Comment has been approved and is visible',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Review   => 'warning',
            self::Accepted => 'success',
        };
    }
}
