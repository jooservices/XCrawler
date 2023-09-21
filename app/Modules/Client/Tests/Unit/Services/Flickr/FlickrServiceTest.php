<?php

namespace App\Modules\Client\Tests\Unit\Services\Flickr;

use App\Modules\Client\Services\FlickrService;
use App\Modules\Client\Tests\TestCase;

class FlickrServiceTest extends TestCase
{
    public function testGetPropertyException()
    {
        $this->expectExceptionMessage('Adapter not found');
        $this->expectException(\Exception::class);
        app(FlickrService::class)->foo;
    }

    public function testContacts()
    {
        $service = app(FlickrService::class)->contacts;
        $list = $service->getList();
        $this->assertCount(1000, $list);
        $this->assertEquals(1, $service->currentPage());
        $this->assertEquals(2, $service->totalPages());
        $this->assertEquals(1102, $service->totalItems());
    }

    public function testPeopleGetPhotos()
    {
        $service = app(FlickrService::class)->people;
        $list = $service->getList(['user_id' => '94529704@N02']);

        $this->assertCount(358, $list);
        $this->assertEquals(1, $service->currentPage());
        $this->assertEquals(1, $service->totalPages());
        $this->assertEquals(358, $service->totalItems());
    }
}
