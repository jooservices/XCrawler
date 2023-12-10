<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class ContactsTest extends TestCase
{
    public function testGetList()
    {
        $adapter = app(FlickrService::class)->setIntegration($this->integration)->contacts;
        $items = $adapter->getList();

        $this->assertCount(1000, $items);
        $this->assertEquals(1, $adapter->currentPage());
        $this->assertEquals(2, $adapter->totalPages());
        $this->assertEquals(1102, $adapter->totalItems());
        $this->assertFalse($adapter->endOfList());

        $items = $adapter->getList(['page' => 2]);
        $this->assertCount(102, $items);
        $this->assertEquals(2, $adapter->currentPage());
        $this->assertEquals(2, $adapter->totalPages());
        $this->assertTrue($adapter->endOfList());
    }

    public function testGetListWithException()
    {
        $adapter = app(FlickrService::class)->setIntegration($this->integration)->contacts;
        $items = $adapter->getList([
            'exception' => true
        ]);

        $this->assertCount(0, $items);
        $this->assertEquals(0, $adapter->currentPage());
        $this->assertEquals(0, $adapter->totalPages());
        $this->assertEquals(0, $adapter->totalItems());
        $this->assertTrue($adapter->endOfList());
    }
}
