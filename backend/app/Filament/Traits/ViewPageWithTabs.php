<?php

declare(strict_types=1);

namespace App\Filament\Traits;


trait ViewPageWithTabs
{
    /**
     * Combine relation managers with content as tabs at the top
     */
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    /**
     * Show relation managers in tabs at the top
     */
    public function getContentTabLabel(): ?string
    {
        return static::$mainTabTitle ?? "Overview";
    }
}
