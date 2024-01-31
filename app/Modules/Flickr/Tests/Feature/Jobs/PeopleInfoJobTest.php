<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Client\StateMachine\Integration\CompletedState;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\FailedException;
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

        $nsid = '94529704@N02';
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

        $contact = FlickrContact::factory()->create(['nsid' => 5]);

        $this->expectException(UserDeletedException::class);
        PeopleInfoJob::dispatch(5);

        $this->assertDatabaseMissing('flickr_contacts', [
            'nsid' => 5
        ]);

        $this->assertTrue($contact->refresh()->trashed());
    }
}
