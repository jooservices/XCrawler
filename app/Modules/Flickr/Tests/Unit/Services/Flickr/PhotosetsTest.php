<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Services\Flickr\Adapters\Photosets;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetEntity;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class PhotosetsTest extends TestCase
{
    private Photosets $adapter;

    public function setUp(): void
    {
        parent::setUp();

        $this->adapter = app(FlickrService::class)->setIntegration($this->integration)->photosets;
    }

    public function testGetList()
    {
        $items = $this->adapter->getList([
            'user_id' => '99097633@N00'
        ]);

        $this->assertCount(47, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(1, $items->getPages());
        $this->assertEquals(47, $items->getTotal());
        $this->assertTrue($items->isCompleted());
    }

    public function testGetPhotos()
    {
        $items = $this->adapter->getPhotos([
            'photoset_id' => 72157674594210788,
            'user_id' => '94529704@N02'
        ]);

        $this->assertCount(1, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(1, $items->getPages());
        $this->assertEquals(1, $items->getTotal());
        $this->assertTrue($items->isCompleted());
    }

    public function testGetInfo()
    {
        $info = $this->adapter->getInfo(72157674594210788);

        $this->assertInstanceOf(PhotosetEntity::class, $info);
        $this->assertEquals(1, $info->photos);
        $this->assertEquals('Phương Trần', $info->title);
    }
}
