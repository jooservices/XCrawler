<?php

namespace App\Modules\Flickr\Tests\Unit\Services\Flickr;

use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\InvalidRespondException;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use App\Modules\Flickr\Exceptions\PermissionDeniedException;
use App\Modules\Flickr\God\Providers\AbstractProvider;
use App\Modules\Flickr\God\Providers\People as GodPeople;
use App\Modules\Flickr\Services\Flickr\Adapters\People;
use App\Modules\Flickr\Services\Flickr\Entities\PeopleInfoEntity;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use GuzzleHttp\Exception\GuzzleException;

class PeopleTest extends TestCase
{
    private People $adapter;

    public function setUp(): void
    {
        parent::setUp();

        $this->adapter = app(FlickrService::class)
            ->setIntegration($this->integration)->people;
    }

    /**
     * @throws MissingEntityElement
     * @throws InvalidRespondException
     * @throws GuzzleException
     * @throws PermissionDeniedException
     */
    public function testGetPhotosUnknownUser()
    {
        $this->expectException(FailedException::class);
        $this->adapter->getPhotos(
            ['user_id' => (string)GodPeople::USER_UNKNOWN_ID]
        );
    }

    public function testGetPhotos()
    {
        $items = $this->adapter->getPhotos(['user_id' => AbstractProvider::NSID]);

        $this->assertCount(358, $items->getItems());
        $this->assertEquals(1, $items->getPage());
        $this->assertEquals(1, $items->getPages());
        $this->assertEquals(358, $items->getTotal());
        $this->assertTrue($items->isCompleted());
    }

    /**
     * @throws MissingEntityElement
     * @throws InvalidRespondException
     * @throws FailedException
     * @throws GuzzleException
     * @throws PermissionDeniedException
     */
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

    /**
     * @throws MissingEntityElement
     * @throws InvalidRespondException
     * @throws FailedException
     * @throws GuzzleException
     * @throws PermissionDeniedException
     */
    public function testGetPeopleInfo()
    {
        $peopleInfo = $this->adapter->getInfo(AbstractProvider::NSID);
        $this->assertInstanceOf(PeopleInfoEntity::class, $peopleInfo);
        $this->assertEquals(AbstractProvider::NSID, $peopleInfo->nsid);
        $this->assertEquals('SoulEvilX', $peopleInfo->username);
    }

    /**
     * @throws MissingEntityElement
     * @throws FailedException
     * @throws GuzzleException
     * @throws PermissionDeniedException
     */
    public function testGetPeopleInfoWithException()
    {
        $this->expectException(InvalidRespondException::class);
        $this->adapter->getInfo('exception');
    }
}
