<?php

declare(strict_types=1);

namespace Panelis\Database\Plugins;

use Filament\Contracts\Plugin;
use Filament\Panel;

class DatabasePlugin implements Plugin
{
    public function getId(): string
    {
        return 'database';
    }

    public function register(Panel $panel): void
    {
        $panel->discoverClusters(__DIR__.'/../Panel/Clusters', 'Panelis\\Database\\Panel\\Clusters');
    }

    public function boot(Panel $panel): void {}
}
