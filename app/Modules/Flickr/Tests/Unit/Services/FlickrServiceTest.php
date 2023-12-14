<?php

namespace App\Modules\Flickr\Tests\Unit\Services;

use App\Modules\Flickr\Jobs\ContactJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class FlickrServiceTest extends TestCase
{
    public function testProcessContact()
    {
        Queue::fake(ContactJob::class);

        app(FlickrService::class)
            ->setIntegration($this->integration)
            ->processContacts();

        Queue::assertPushed(ContactJob::class, function ($job) {
            return $job->page === 2;
        });
    }

    public function testProcessContactWithEndOfPages()
    {
        Queue::fake(ContactJob::class);

        app(FlickrService::class)
            ->setIntegration($this->integration)
            ->processContacts(2);

        Queue::assertNotPushed(ContactJob::class);
    }
}
