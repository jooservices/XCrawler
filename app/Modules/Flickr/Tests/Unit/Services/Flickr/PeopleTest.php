<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Services\Flickr\Adapters\People;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use InvalidArgumentException;

class PeopleTest extends TestCase
{
    private People $adapter;
    public function setUp(): void
    {
        parent::setUp();

        $this->adapter =  app(FlickrService::class)->setIntegration($this->integration)->people;
    }

    public function testGetPhotosUnknownUser()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid data');


        $this->adapter->getPhotos(['user_id' => '44203036@N06']);
    }

    public function testGetPhotos()
    {
        $items = $this->adapter->getPhotos(['user_id' => '94529704@N02']);

        $this->assertCount(358, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(1, $items->getPages());
        $this->assertEquals(358, $items->getTotal());
        $this->assertTrue($items->isCompleted());
    }

    public function testGetPhotosHavePages()
    {
        $items = $this->adapter->getPhotos(['user_id' => '73115043@N07']);

        $this->assertCount(500, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(2, $items->getPages());
        $this->assertEquals(507, $items->getTotal());
        $this->assertFalse($items->isCompleted());
        $this->assertEquals(2, $items->getNextPage());
    }
}
