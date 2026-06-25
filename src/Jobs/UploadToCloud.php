<?php

namespace Panelis\Database\Jobs;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Panelis\Database\Enums\Disk;

class UploadToCloud implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private readonly string $relativePath)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        [$time, $ext] = explode('.', basename($this->relativePath), 2);
        $name = Carbon::createFromTimestamp($time)
            ->timezone(get_timezone())
            ->format('Y-m-d_H-i');
        $name = sprintf('%s-%s.%s', app()->environment(), $name, $ext);

        $local = Storage::disk(Disk::Local);
        if (! $local->exists($this->relativePath)) {
            Log::warning('Database file not found', [
                'path' => $this->relativePath,
            ]);

            return;
        }

        $stream = Storage::disk(Disk::Local)->readStream($this->relativePath);

        $x = Storage::put($name, $stream);

        fclose($stream);
    }
}
