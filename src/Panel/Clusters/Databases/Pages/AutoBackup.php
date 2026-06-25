<?php

namespace Panelis\Database\Panel\Clusters\Databases\Pages;

use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Panelis\Database\Enums\Disk;
use Panelis\Database\Jobs\Backup;
use Panelis\Database\Panel\Clusters\Databases;
use Panelis\Database\Panel\Clusters\Databases\Enums\DatabasePermission;
use Panelis\Database\Panel\Clusters\Databases\Forms\AutoBackupForm;
use Panelis\Database\Services\Database\Contracts\Database;
use Panelis\Database\Services\Database\Database as DatabaseManager;
use Panelis\Database\Services\Database\Enums\DatabaseDriver;
use Panelis\Setting\Events\SettingUpdated;
use Panelis\Setting\Models\Setting;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AutoBackup extends Page implements HasSchemas
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected string $view = 'filament.clusters.databases.pages.auto-backup';

    protected static ?string $cluster = Databases::class;

    protected static ?int $navigationSort = 2;

    public array $database;

    public array $filesystems;

    public array $services;

    private ?Database $databaseContract = null;

    public bool $isSupported = true;

    protected function getUpdateAction(): Action
    {
        return Action::make('update_setting')
            ->label(__('ui.btn.update'))
            ->disabled(user_cannot(DatabasePermission::Edit))
            ->action('update');
    }

    public static function getNavigationLabel(): string
    {
        return __('database::database.auto_backup.label');
    }

    public function getTitle(): string|Htmlable
    {
        return __('database::database.auto_backup.label');
    }

    public static function canAccess(): bool
    {
        return user_can(DatabasePermission::Browse);
    }

    public function boot(DatabaseManager $manager): void
    {
        $driver = config('database.default');

        if (! DatabaseDriver::isSupported($driver)) {
            $this->isSupported = false;

            return;
        }

        try {
            $this->databaseContract = $manager->driver($driver);
        } catch (Throwable $e) {
            Log::warning("Database driver [$driver] could not be initialized.", [
                'exception' => $e,
            ]);

            $this->isSupported = false;
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backup')
                ->visible(user_can(DatabasePermission::Backup))
                ->label(__('database::database.btn.backup_now'))
                ->hidden(! $this->isSupported)
                ->schema([
                    Callout::make(__('database::database.callouts.storage_disabled.title'))
                        ->description(__('database::database.callouts.storage_disabled.description'))
                        ->info()
                        ->visible(config('filesystems.default') === Disk::Local->value),

                    Section::make()
                        ->hidden(config('filesystems.default') === Disk::Local->value)
                        ->schema([
                            Toggle::make('upload_to_storage')
                                ->label(__('database::database.upload_to_storage', ['storage' => config('filesystems.default')])),
                        ]),
                ])
                ->requiresConfirmation()
                ->action(function (array $data): void {
                    $data['users'] = [Auth::id()];
                    Backup::dispatch($this->databaseContract, $data);

                    Notification::make()
                        ->title(__('database::database.notifications.queue.title'))
                        ->body(__('database::database.notifications.queue.body'))
                        ->success()
                        ->send();
                }),
        ];
    }

    public function mount(): void
    {
        if (! $this->isSupported) {
            Setting::where('key', 'database.auto_backup_enabled')->delete();
            config()->set('database.auto_backup_enabled', false);
        }

        $this->form->fill([
            'isButtonDisabled' => ! $this->isSupported || ! user_can(DatabasePermission::Edit),

            'database' => [
                'auto_backup_enabled' => config('database.auto_backup_enabled', false),
                'backup_period' => config('database.backup_period'),
                'backup_time' => config('database.backup_time', '00:00'),
                'backup_max' => config('database.backup_max', 3),

                // cloud backup settings
                'cloud_backup_enabled' => config('database.cloud_backup_enabled', false),
                'cloud_storage' => config('database.cloud_storage'),
            ],

            'filesystems' => [
                'default' => config('filesystems.default'),
            ],
        ]);
    }

    public function form(Schema $schema): Schema
    {
        $driver = DatabaseDriver::tryFrom(config('database.default'));

        return $schema
            ->components([
                Callout::make(__('database::database.not_supported'))
                    ->description(__('database::database.not_supported_reason', ['driver' => $driver?->getLabel()]))
                    ->hidden($this->isSupported)
                    ->warning(),

                Section::make(__('database::database.auto_backup.label'))
                    ->description(__('database::database.auto_backup.section_description'))
                    ->hidden(! $this->isSupported)
                    ->schema(AutoBackupForm::schema($this->databaseContract)),
            ])
            ->disabled(user_cannot(DatabasePermission::Edit));
    }

    /**
     * @throws ValidationException
     */
    public function update(): void
    {
        abort_unless(user_can(DatabasePermission::Edit), Response::HTTP_FORBIDDEN);

        $this->validate();

        try {
            foreach (Arr::dot($this->form->getState()) as $key => $value) {
                Setting::updateOrCreate(compact('key'), compact('value'));
            }

            event(new SettingUpdated);

            Notification::make()
                ->title(__('database::database.backup_updated'))
                ->success()
                ->send();
        } catch (Exception $e) {
            Log::error($e);

            Notification::make()
                ->title(__('database::database.backup_not_updated'))
                ->danger()
                ->send();
        }
    }
}
