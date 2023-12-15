<?php

namespace App\Modules\Flickr\Tests\Unit\Services;

use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class FlickrServiceTest extends TestCase
{
    public function testMissingProvider()
    {
        $this->expectException(\Exception::class);

        app(FlickrService::class)->test;
    }

    public function testInvalidAdapter()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Adapter not found');

        app(FlickrService::class)->setIntegration($this->integration)
            ->test;
    }
}
