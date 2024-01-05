<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Exceptions\MissingEntityElement;
use App\Modules\Flickr\Services\Flickr\Adapters\People;
use App\Modules\Flickr\Services\Flickr\Entities\PeopleInfoEntity;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class PeopleTest extends TestCase
{
    private People $adapter;

    public function setUp(): void
    {
        parent::setUp();

        $this->adapter = app(FlickrService::class)->setIntegration($this->integration)->people;
    }

    public function testGetPhotosUnknownUser()
    {
        $this->expectException(MissingEntityElement::class);
        $this->expectExceptionMessage('Missing element "photos" in response');


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

    public function testGetPeopleInfo()
    {
        $nsid = '16842686@N04';

        $peopleInfo = $this->adapter->getInfo($nsid);
        $this->assertInstanceOf(PeopleInfoEntity::class, $peopleInfo);
        $this->assertEquals($nsid, $peopleInfo->nsid);
        $this->assertEquals('Michigan, USA', $peopleInfo->location);
    }
}
