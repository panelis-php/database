<?php

namespace Panelis\Database\Panel\Clusters;

use Filament\Clusters\Cluster;
use Panelis\Database\Panel\Clusters\Databases\Enums\DatabasePermission;

class Databases extends Cluster
{
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return __('ui.system');
    }

    public static function getNavigationLabel(): string
    {
        return __('database::database.navigation');
    }

    public static function canAccess(): bool
    {
        return user_can(DatabasePermission::Browse);
    }
}
