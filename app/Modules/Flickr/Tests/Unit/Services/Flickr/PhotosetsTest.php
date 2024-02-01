<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\God\Providers\AbstractProvider;
use App\Modules\Flickr\Services\Flickr\Adapters\Photosets;
use App\Modules\Flickr\Services\Flickr\Entities\PhotosetEntity;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use GuzzleHttp\Exception\GuzzleException;

class PhotosetsTest extends TestCase
{
    private Photosets $adapter;

    public function setUp(): void
    {
        parent::setUp();

        $this->adapter = app(FlickrService::class)->setIntegration($this->integration)->photosets;
    }

    /**
     * @throws MissingEntityElement
     * @throws InvalidRespondException
     * @throws FailedException
     * @throws GuzzleException
     */
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

    /**
     * @throws MissingEntityElement
     * @throws FailedException
     * @throws InvalidRespondException
     * @throws GuzzleException
     */
    public function testGetPhotos()
    {
        $items = $this->adapter->getPhotos([
            'photoset_id' => AbstractProvider::PHOTOSET_ID,
            'user_id' => AbstractProvider::NSID
        ]);

        $this->assertCount(1, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(2, $items->getPages());
        $this->assertEquals(2, $items->getTotal());

        $this->assertFalse($items->isCompleted());
    }

    /**
     * @throws GuzzleException
     * @throws FailedException
     * @throws InvalidRespondException
     */
    public function testGetInfo()
    {
        $info = $this->adapter->getInfo(72157674594210788);

        $this->assertInstanceOf(PhotosetEntity::class, $info);
        $this->assertEquals(1, $info->photos);
        $this->assertEquals('Phương Trần', $info->title);
    }

    /**
     * @throws GuzzleException
     * @throws FailedException
     */
    public function testGetInfoWithException()
    {
        $this->expectException(InvalidRespondException::class);
        $this->adapter->getInfo(-1);
    }
}
