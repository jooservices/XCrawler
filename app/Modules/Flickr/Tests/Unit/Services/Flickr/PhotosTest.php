<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class PhotosTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->adapter = app(FlickrService::class)->setIntegration($this->integration)->photos;
    }

    public function testGetSizesWithException()
    {
        $this->expectException(InvalidRespondException::class);
        $this->assertNull($this->adapter->getSizes(-1));
    }

    public function testGetSizesWithNoSize()
    {
        $this->expectException(MissingEntityElement::class);
        $this->adapter->getSizes(-2);
    }
}
