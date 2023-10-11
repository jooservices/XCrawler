<?php

namespace App\Modules\Client\Tests\Unit\Services\Flickr;

use App\Modules\Client\Services\FlickrManager;
use App\Modules\Client\Tests\TestCase;

class ContactsTest extends TestCase
{
    public function testGetList()
    {
        $service = app(FlickrManager::class)->contacts;
        $items = $service->getList();

        $this->assertCount(1000, $items);
        $this->assertEquals(1, $service->currentPage());
        $this->assertEquals(2, $service->totalPages());
        $this->assertEquals(1102, $service->totalItems());
        $this->assertFalse($service->endOfList());

        $items = $service->getList(['page' => 2]);
        $this->assertCount(102, $items);
        $this->assertEquals(2, $service->currentPage());
        $this->assertEquals(2, $service->totalPages());
        $this->assertTrue($service->endOfList());
    }

    public function testGetListWithException()
    {
        $service = app(FlickrManager::class)->contacts;
        $items = $service->getList([
            'exception' => true
        ]);

        $this->assertCount(0, $items);
        $this->assertEquals(0, $service->currentPage());
        $this->assertEquals(0, $service->totalPages());
        $this->assertEquals(0, $service->totalItems());
        $this->assertTrue($service->endOfList());
    }
}
