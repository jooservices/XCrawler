<?php

namespace App\Modules\Flickr\Tests\Unit\Repositories;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Repositories\ContactRepository;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;

class ContactRepositoryTest extends TestCase
{
    private ContactRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        $this->repository = app(ContactRepository::class);
    }

    /**
     * Make sure we will not create duplicate contacts
     * @return void
     */
    public function testCreateDuplicate()
    {
        $contact = $this->repository->create([
            'nsid' => $this->faker->uuid,
        ]);

        $this->assertDatabaseHas('flickr_contacts', [
            'nsid' => $contact->nsid,
        ]);

        $contact2 = $this->repository->create([
            'nsid' => $contact->nsid
        ]);

        $this->assertTrue($contact2->is($contact));
    }

    public function testGetContactsForPhotos()
    {
        $this->assertEmpty($this->repository->getContactsForTask(TaskService::TASK_CONTACT_PHOTOS));

        /**
         * Create a contact via factory will not create tasks
         */
        FlickrContact::factory()->create();
        $this->assertCount(1, $this->repository->getContactsForTask(TaskService::TASK_CONTACT_PHOTOS));
    }
}
