<?php

namespace App\Modules\Core\Tests\Unit\Services;

use App\Modules\Client\Services\Downloader;
use App\Modules\Core\Events\FileDownloaded;
use App\Modules\Core\Services\FileManager;
use App\Modules\Core\Tests\TestCase;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

class FileManagerTest extends TestCase
{
    public function testInvalidUrl()
    {
        $this->expectException(Exception::class);
        app(FileManager::class)->download('invalid-url');
    }

    public function testDownloadSuccess()
    {
        Event::fake(FileDownloaded::class);
        $this->instance(
            Downloader::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                $mock->shouldReceive('download')
                    ->once()
                    ->andReturn($this->faker->numerify());
            })
        );

        $service = app(FileManager::class);
        $url = $this->faker->url . '/' . $this->faker->word . '.pdf';
        $service->download($url);

        Event::dispatched(FileDownloaded::class, function (FileDownloaded $event) use ($url) {
            $this->assertEquals($url, $event->url);
            $this->assertStringContainsString(FileManager::DOWNLOAD_PATH, $event->saveTo);
            return true;
        });
    }
}
