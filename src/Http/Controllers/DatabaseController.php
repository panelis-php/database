<?php

namespace Panelis\Database\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Panelis\Database\Actions\Download;
use Panelis\Database\Enums\Disk;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseController extends Controller
{
    public function download(string $file): StreamedResponse
    {
        abort_if(str_contains($file, '..'), 404);

        $file = sprintf('database/%s', $file);
        $storage = Storage::disk(Disk::Local);

        abort_unless($storage->exists($file), 404);

        return Download::run($storage, $file, basename($file));
    }
}
