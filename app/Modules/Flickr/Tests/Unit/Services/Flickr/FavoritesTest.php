<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Exceptions\PermissionDeniedException;
use App\Modules\Flickr\God\Providers\AbstractProvider;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use GuzzleHttp\Exception\GuzzleException;

class FavoritesTest extends TestCase
{
    /**
     * @throws MissingEntityElement
     * @throws FailedException
     * @throws InvalidRespondException
     * @throws GuzzleException
     * @throws PermissionDeniedException
     */
    public function testGetList()
    {
        $adapter = app(FlickrService::class)->setIntegration($this->integration)->favorites;
        $items = $adapter->getList(['user_id' => AbstractProvider::NSID]);

        $this->assertCount(417, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(4, $items->getPages());
        $this->assertEquals(1645, $items->getTotal());
        $this->assertFalse($items->isCompleted());
        $this->assertEquals(2, $items->getNextPage());
    }
}
