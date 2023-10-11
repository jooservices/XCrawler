<?php

namespace App\Modules\Client\Tests\Unit\Services\Flickr;

use App\Modules\Client\OAuth\Exceptions\FlickrRequestLimit;
use App\Modules\Client\Services\FlickrManager;
use App\Modules\Client\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class FlickrManagerTest extends TestCase
{
    public function testGetPropertyException()
    {
        $this->expectExceptionMessage('Adapter not found');
        $this->expectException(\Exception::class);
        app(FlickrManager::class)->foo;
    }

    public function testContacts()
    {
        $service = app(FlickrManager::class)->contacts;
        $list = $service->getList();
        $this->assertCount(1000, $list);
        $this->assertEquals(1, $service->currentPage());
        $this->assertEquals(2, $service->totalPages());
        $this->assertEquals(1102, $service->totalItems());

        $this->assertEquals(1, Cache::get('flickr_request_count'));
    }

    public function testFlickrRequestLimit() {
        $this->expectException(FlickrRequestLimit::class);
        Cache::set('flickr_request_count', 3600);

        $service = app(FlickrManager::class)->contacts;
        $service->getList();
    }

    public function testPeopleGetPhotos()
    {
        $service = app(FlickrManager::class)->people;
        $list = $service->getList(['user_id' => '94529704@N02']);

        $this->assertCount(358, $list);
        $this->assertEquals(1, $service->currentPage());
        $this->assertEquals(1, $service->totalPages());
        $this->assertEquals(358, $service->totalItems());
    }
}
