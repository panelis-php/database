<?php

namespace Panelis\Database\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Panelis\Database\Commands\BackupCommand;
use Panelis\Database\Services\Database\Contracts\Database;
use Panelis\Database\Services\Database\Database as DatabaseManager;

class DatabaseServiceProvider extends ServiceProvider
{
    private const string NAMESPACE = 'database';

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../../lang', self::NAMESPACE);

        if ($this->app->runningInConsole()) {
            $this->commands([
                BackupCommand::class,
            ]);
        }

        Route::middleware(['auth', 'web'])
            ->prefix('panelis.database')
            ->name('panelis.database.')
            ->group(__DIR__.'/../../routes/web.php');
    }

    public function register(): void
    {
        $this->app->singleton(Database::class, function (Application $app): Database {
            return $app->make(DatabaseManager::class)->driver(config('database.default'));
        });
    }
}
