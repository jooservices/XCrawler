<?php

namespace App\Modules\Client\Tests\Unit\Services\Flickr;

use App\Modules\Client\OAuth\Exceptions\RequestLimited;
use App\Modules\Client\Services\FlickrManager;
use App\Modules\Client\Tests\TestCase;
use Illuminate\Support\Facades\Cache;

class FlickrManagerTest extends TestCase
{
    private FlickrManager $flickr;

    public function setUp(): void
    {
        parent::setUp();

        $this->flickr = app(FlickrManager::class);
    }

    public function testGetPropertyException()
    {
        $this->expectExceptionMessage('Adapter not found');
        $this->expectException(\Exception::class);

        $this->flickr->foo;
    }

    public function testContacts()
    {
        $service = $this->flickr->contacts;
        $list = $service->getList();
        $this->assertCount(1000, $list);
        $this->assertEquals(1, $service->currentPage());
        $this->assertEquals(2, $service->totalPages());
        $this->assertEquals(1102, $service->totalItems());
    }

    public function testPeopleGetPhotos()
    {
        $service = $this->flickr->people;
        $list = $service->getList(['user_id' => '94529704@N02']);

        $this->assertCount(358, $list);
        $this->assertEquals(1, $service->currentPage());
        $this->assertEquals(1, $service->totalPages());
        $this->assertEquals(358, $service->totalItems());
    }
}
