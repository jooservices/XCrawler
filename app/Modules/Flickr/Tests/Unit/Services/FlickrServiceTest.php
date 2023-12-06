<?php

namespace App\Modules\Flickr\Tests\Unit\Services;

use App\Modules\Flickr\Jobs\ContactJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class FlickrServiceTest extends TestCase
{
    private FlickrService $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->service = app(FlickrService::class);
    }

    public function testContacts()
    {
        Queue::fake(ContactJob::class);

        $this->service->contacts();

        Queue::assertPushed(ContactJob::class, function ($job) {
            return $job->page === 2;
        });
    }
}
