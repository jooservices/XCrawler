<?php

namespace App\Modules\Flickr\Tests\Unit\Services;

use App\Modules\Flickr\Services\Flickr\Entities\ContactsListEntity;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Exception;

class FlickrServiceTest extends TestCase
{
    public function testMissingProvider()
    {
        $this->expectException(Exception::class);

        app(FlickrService::class)->test;
    }

    public function testInvalidAdapter()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Adapter not found');

        app(FlickrService::class)->setIntegration($this->integration)
            ->test;
    }

    public function testWithoutIntegration()
    {
        $service = app(FlickrService::class);
        $contacts = $service->contacts;

        $this->assertInstanceOf(ContactsListEntity::class, $contacts->getList());
    }
}
