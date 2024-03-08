<?php

namespace App\Modules\Client\Services;

use App\Modules\Client\Exceptions\DownloadFailedException;
use App\Modules\Client\Exceptions\InvalidUrlException;
use Exception;

class Downloader
{
    /**
     * @throws Exception
     */
    public function download(string $url, string $saveTo, string $method = 'GET'): false|int
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidUrlException($url);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLINFO_HEADER_OUT, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        $respond = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new DownloadFailedException('Download error: ' . curl_error($ch));
        }

        return file_put_contents($saveTo, $respond);
    }
}
