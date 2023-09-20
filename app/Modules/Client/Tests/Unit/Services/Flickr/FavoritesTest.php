<?php

namespace App\Modules\Client\Tests\Unit\Services\Flickr;

use App\Modules\Client\Services\FlickrService;
use App\Modules\Client\Tests\TestCase;

class FavoritesTest extends TestCase
{
    public function testGetList()
    {
        $service = app(FlickrService::class)->favorites;
        $items = $service->getList([
            'user_id' => '94529704@N02'
        ]);

        $this->assertCount(417, $items);
        $this->assertEquals(1, $service->currentPage());
        $this->assertEquals(4, $service->totalPages());
        $this->assertEquals(1645, $service->totalItems());
        $this->assertFalse($service->endOfList());
        $this->assertEquals(2, $service->nextPage());
    }
}
