<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

trait HasActiveColumn
{
    #[Scope]
    public function active(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    #[Scope]
    public function disabled(Builder $query): Builder
    {
        return $query->where('is_active', false);
    }

    #[Scope]
    public function whereActiveIs(Builder $query, bool $isActive): Builder
    {
        return $query->where('is_active', $isActive);
    }
}
