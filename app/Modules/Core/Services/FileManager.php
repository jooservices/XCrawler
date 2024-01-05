<?php

namespace App\Modules\Core\Services;

use _PHPStan_cb6cd3c76\Nette\Neon\Exception;
use Illuminate\Contracts\Filesystem\Filesystem;

class FileManager
{
    public function __construct(
        private Filesystem $storage,
    ) {
    }

    public function download(string $url): string
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new Exception('Invalid URL');
        }

        $basenameWithoutParameters = explode(
            '?',
            pathinfo(
                $url,
                PATHINFO_BASENAME
            )
        )[0];

        $content = file_get_contents($url);
        $this->storage->put($basenameWithoutParameters, $content);

        return $basenameWithoutParameters;
    }
}
