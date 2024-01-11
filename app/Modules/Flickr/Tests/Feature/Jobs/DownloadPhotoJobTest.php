<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Client\Services\Downloader;
use App\Modules\Core\Events\FileDownloaded;
use App\Modules\Flickr\Events\PhotosetPhotoDownloadCompletedEvent;
use App\Modules\Flickr\Jobs\DownloadPhotoJob;
use App\Modules\Flickr\Models\FlickrPhoto;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Event;
use Mockery;
use Mockery\MockInterface;

class DownloadPhotoJobTest extends TestCase
{
    public function testDownload()
    {
        Event::fake([
            PhotosetPhotoDownloadCompletedEvent::class,
            FileDownloaded::class
        ]);
        $photo = FlickrPhoto::factory()->create([
            'sizes' => [
                [
                    'source' => 'https://live.staticflickr.com//65535//53312842788_9831c0d67a_s.jpg'
                ],
                [
                    'source' => 'https://live.staticflickr.com//65535//53312842788_9831c0d67a_m.jpg'
                ],
                [
                    'source' => 'https://live.staticflickr.com//65535//53312842788_9831c0d67a_o.jpg'
                ]
            ]
        ]);

        $task = $photo->tasks()->create([
            'task' => FlickrService::TASK_DOWNLOAD_PHOTOSET_PHOTO,
            'payload' => [
                'photos' => 1
            ]
        ]);

        $this->instance(
            Downloader::class,
            Mockery::mock(Client::class, function (MockInterface $mock) {
                $mock->shouldReceive('download')
                    ->once()
                    ->andReturn($this->faker->numerify());
            })
        );

        DownloadPhotoJob::dispatch($task);

        Event::assertDispatched(PhotosetPhotoDownloadCompletedEvent::class);
        Event::assertDispatched(FileDownloaded::class);
    }
}
