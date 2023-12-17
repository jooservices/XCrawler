<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Exceptions\InvalidRespondException;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class ContactsTest extends TestCase
{
    public function testGetList()
    {
        $adapter = app(FlickrService::class)->setIntegration($this->integration)->contacts;
        $items = $adapter->getList();

        $this->assertCount(1000, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(2, $items->getPages());
        $this->assertEquals(1102, $items->getTotal());

        $items = $adapter->getList(['page' => 2]);
        $this->assertCount(102, $items->getItems());
        $this->assertEquals(2, $items->getPage());
        $this->assertEquals(2, $items->getPages());
        $this->assertEquals(1000, $items->getPerPage());
        $this->assertTrue($items->isCompleted());
    }

    public function testGetListWithException()
    {
        $this->expectException(InvalidRespondException::class);

        $adapter = app(FlickrService::class)->setIntegration($this->integration)->contacts;
        $adapter->getList([
            'exception' => true
        ]);
    }
}
