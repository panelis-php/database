<?php

declare(strict_types=1);

namespace Panelis\Database\Notifications;

use Filament\Actions\Action;
use Filament\Notifications\Notification as NotificationsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Panelis\Database\Panel\Clusters\Databases\Pages\Backup;

class BackupNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return NotificationsNotification::make()
            ->title(__('database::database.notifications.backup.title'))
            ->body(__('database::database.notifications.backup.body'))
            ->success()
            ->actions([
                Action::make('view_backup')
                    ->label(__('database::database.btn.view'))
                    ->url(Backup::getUrl()),
            ])
            ->getDatabaseMessage();
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
