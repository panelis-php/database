<?php

namespace Panelis\Database\Providers;

use Composer\InstalledVersions;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use League\Flysystem\Filesystem;
use Panelis\Database\Commands\BackupCommand;
use Panelis\Database\Panel\Clusters\Databases\Enums\CloudProvider;
use Panelis\Database\Services\Database\Contracts\Database;
use Panelis\Database\Services\Database\Database as DatabaseManager;
use SocialiteProviders\Dropbox\Provider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use Spatie\Dropbox\Client;
use Spatie\FlysystemDropbox\DropboxAdapter;

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

        if (InstalledVersions::isInstalled('socialiteproviders/dropbox')) {
            Event::listen(function (SocialiteWasCalled $event) {
                $event->extendSocialite('dropbox', Provider::class);
            });
        }

        if (InstalledVersions::isInstalled('spatie/flysystem-dropbox')) {
            Storage::extend(CloudProvider::Dropbox->value, function (Application $app, array $config): FilesystemAdapter {
                $adapter = new DropboxAdapter(new Client(config('filesystems.disks.dropbox.token')));

                return new FilesystemAdapter(
                    new Filesystem($adapter, $config),
                    $adapter,
                    $config,
                );
            });
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
