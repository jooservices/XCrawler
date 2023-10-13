<?php

namespace App\Modules\Flickr\Tests\Unit\Repositories;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Repositories\ContactRepository;
use App\Modules\Flickr\Tests\TestCase;

class ContactRepositoryTest extends TestCase
{
    private ContactRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ContactRepository::class);
    }

    public function testGetContactsForPhotos()
    {
        $this->assertEmpty($this->repository->getContactsForPhotos());

        $contact = FlickrContact::create(['nsid' => '123',]);
        FlickrContact::create(['nsid' => '1234', 'state_code' => States::STATE_IN_PROGRESS]);
        FlickrContact::create(['nsid' => '12345', 'state_code' => States::STATE_COMPLETED]);

        $this->assertTrue(
            $contact->is($this->repository->getContactsForPhotos()->first())
        );

        FlickrContact::query()->update([
            'state_code' => States::STATE_COMPLETED
        ]);

        $this->assertCount(3, $this->repository->getContactsForPhotos(3));
    }

    public function testGetContactsForFavorites()
    {
        $this->assertEmpty($this->repository->getContactForFavorites());

        $contact = FlickrContact::create(['nsid' => '123',]);
        FlickrContact::create(['nsid' => '1234', 'favorites_state_code' => States::STATE_IN_PROGRESS]);
        FlickrContact::create(['nsid' => '12345', 'favorites_state_code' => States::STATE_COMPLETED]);

        $this->assertTrue(
            $contact->is($this->repository->getContactForFavorites()->first())
        );

        FlickrContact::query()->update([
            'favorites_state_code' => States::STATE_COMPLETED
        ]);

        $this->assertCount(3, $this->repository->getContactForFavorites(3));
    }
}
