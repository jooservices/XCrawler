<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Tests\TestCase;
use App\Modules\Flickr\Services\FlickrService;

class FavoritesTest extends TestCase
{
    public function testGetList()
    {
        $adapter = app(FlickrService::class)->setIntegration($this->integration)->favorites;
        $items = $adapter->getList(['user_id' => '94529704@N02']);

        $this->assertCount(417, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(4, $items->getPages());
        $this->assertEquals(1645, $items->getTotal());
        $this->assertFalse($items->isCompleted());
        $this->assertEquals(2, $items->getNextPage());
    }
}
