<?php

namespace App\Modules\Flickr\Tests\Unit\Repositories;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Repositories\ContactRepository;
use App\Modules\Flickr\Services\FlickrService;
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
        $this->assertEmpty($this->repository->getContactsForTask(FlickrService::TASK_CONTACT_PHOTOS));

        /**
         * Create a contact via factory will not create tasks
         */
        FlickrContact::factory()->create();
        $this->assertCount(1, $this->repository->getContactsForTask(FlickrService::TASK_CONTACT_PHOTOS));
    }
}
