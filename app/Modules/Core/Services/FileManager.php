<?php

namespace App\Modules\Core\Services;

use App\Modules\Client\Services\Downloader;
use App\Modules\Core\Events\FileDownloaded;
use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;

class FileManager
{
    public const DOWNLOAD_PATH = 'downloads';

    public function __construct(
        private Filesystem $storage,
    ) {
    }

    /**
     * @throws Exception
     */
    public function download(string $url, ?string $fileName = null): int
    {
        if (!$this->storage->exists(self::DOWNLOAD_PATH)) {
            $this->storage->makeDirectory(self::DOWNLOAD_PATH);
        }

        $fileName = $fileName ?? explode('?', pathinfo($url, PATHINFO_BASENAME))[0];
        $saveTo = self::DOWNLOAD_PATH . '/' . $fileName;

        $return = app(Downloader::class)->download($url, $this->storage->path($saveTo));

        if ($return === false) {
            throw new Exception('Download error');
        }

        Event::dispatch(new FileDownloaded($url, $saveTo));

        return $return;
    }
}
