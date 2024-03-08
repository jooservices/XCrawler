<?php

namespace App\Modules\Client\Tests\Unit\Services;

use App\Modules\Client\Exceptions\InvalidUrlException;
use App\Modules\Client\Services\Downloader;
use App\Modules\Client\Tests\TestCase;

class DownloaderTest extends TestCase
{
    public function testDownloadInvalidUrl(): void
    {
        $downloader = app(Downloader::class);

        try {
            $downloader->download('invalid-url', 'save-to');
        } catch (InvalidUrlException $e) {
            $this->assertEquals('invalid-url', $e->getUrl());
        }
    }
}
