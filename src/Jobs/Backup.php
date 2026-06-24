<?php

declare(strict_types=1);

namespace Panelis\Database\Jobs;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Panelis\Database\Notifications\BackupNotification;
use Panelis\Database\Services\Database\Contracts\Database;
use Panelis\User\Models\User;

class Backup implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private readonly Database $database,
        private readonly array $data,
    ) {}

    public function handle(): void
    {
        try {
            $path = $this->database->backup();

            // upload to cloud if possible
            if ($this->data['upload_to_cloud'] ?? false) {
                UploadToCloud::dispatch($path);
            }

            if (! empty($this->data['users'])) {
                $users = User::query()
                    ->whereIn('id', $this->data['users'])
                    ->get();

                Notification::send($users, new BackupNotification);
            }
        } catch (Exception $e) {
            Log::error($e);
        }
    }
}
