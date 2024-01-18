<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Flickr\Exceptions\UserDeletedException;
use App\Modules\Flickr\Jobs\PeopleInfoJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class PeopleInfoJobTest extends TestCase
{
    public function testGetPeopleInfo()
    {
        Integration::factory()->create([
            'is_primary' => false,
            'service' => FlickrService::SERVICE_NAME,
            'state_code' => CompletedState::class
        ]);

        $nsid = '16842686@N04';
        PeopleInfoJob::dispatch($nsid);

        $this->assertDatabaseHas('flickr_contacts', [
            'nsid' => $nsid
        ]);
    }

    public function testGetPeopleInfoWithDeleted()
    {
        Integration::factory()->create([
            'is_primary' => false,
            'service' => FlickrService::SERVICE_NAME,
            'state_code' => CompletedState::class
        ]);

        FlickrContact::factory()->create([
            'nsid' => 'User deleted'
        ]);

        $this->expectException(UserDeletedException::class);
        PeopleInfoJob::dispatch('User deleted');
        $this->assertDatabaseMissing('flickr_contacts', [
            'nsid' => 'User deleted'
        ]);
    }
}
