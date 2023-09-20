<?php

namespace App\Modules\Flickr\Tests\Unit\Repositories;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Models\FlickrContacts;
use App\Modules\Flickr\Repositories\ContactRepository;
use Tests\TestCase;

class ContactRepositories extends TestCase
{
    public function testGetContactsForPhotos()
    {
        FlickrContacts::truncate();
        $service = app(ContactRepository::class);
        $this->assertEmpty($service->getContactsForPhotos());

        $contact = FlickrContacts::create(['nsid' => '123',]);
        FlickrContacts::create(['nsid' => '1234', 'state_code' => States::STATE_IN_PROGRESS]);
        FlickrContacts::create(['nsid' => '12345', 'state_code' => States::STATE_COMPLETED]);

        $this->assertTrue(
            $contact->is($service->getContactsForPhotos()->first())
        );

        FlickrContacts::query()->update([
            'state_code' => States::STATE_COMPLETED
        ]);

        $this->assertCount(3,$service->getContactsForPhotos(3));
    }
}
