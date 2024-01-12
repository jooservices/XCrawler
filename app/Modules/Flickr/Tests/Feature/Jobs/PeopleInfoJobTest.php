<?php

namespace App\Modules\Flickr\Tests\Feature\Jobs;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\PeopleInfoJob;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;

class PeopleInfoJobTest extends TestCase
{
    public function testGetPeopleInfo()
    {
        Integration::factory()->create([
            'is_primary' => false,
            'service' => FlickrService::SERVICE_NAME,
            'state_code' => States::STATE_COMPLETED
        ]);

        $nsid = '16842686@N04';
        PeopleInfoJob::dispatch($nsid);

        $this->assertDatabaseHas('flickr_contacts', [
            'nsid' => $nsid
        ]);
    }
}
