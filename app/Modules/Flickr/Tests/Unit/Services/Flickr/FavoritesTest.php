<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Client\Services\FlickrManager;
use App\Modules\Client\Tests\TestCase;
use App\Modules\Flickr\Services\FlickrService;

class FavoritesTest extends TestCase
{
    public function testGetList()
    {
        $adapter = app(FlickrService::class)->setIntegration($this->integration)->favorites;
        $items = $adapter->getList([
            'user_id' => '94529704@N02'
        ]);

        $this->assertCount(417, $items);
        $this->assertEquals(1, $adapter->currentPage());
        $this->assertEquals(4, $adapter->totalPages());
        $this->assertEquals(1645, $adapter->totalItems());
        $this->assertFalse($adapter->endOfList());
        $this->assertEquals(2, $adapter->nextPage());
    }
}
