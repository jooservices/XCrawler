<?php

namespace App\Modules\Flickr\Tests\God;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Core\God\Generator;
use App\Modules\Core\Tests\TestCase;
use App\Modules\Flickr\Services\Flickr\Entities\ContactsListEntity;
use App\Modules\Flickr\Services\FlickrService;

class TestContacts extends TestCase
{
    public function testContacts()
    {
        Integration::factory()->create([
            'state_code' => CompletedState::class
        ]);
        $god = app(Generator::class);
        $god->mockClient('flickr', [
            ['contacts']
        ]);

        $contacts = app(FlickrService::class)
            ->contacts
            ->getList();

        $this->assertInstanceOf(ContactsListEntity::class, $contacts);
    }
}
