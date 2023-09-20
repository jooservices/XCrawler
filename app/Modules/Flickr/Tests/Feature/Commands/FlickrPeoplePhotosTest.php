<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Client\Tests\TestCase;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\FlickrPhotos;
use App\Modules\Flickr\Models\FlickrContacts;
use Illuminate\Support\Facades\Bus;

class FlickrPeoplePhotosTest extends TestCase
{
    public function testCommand()
    {
        FlickrContacts::truncate();
        Bus::fake();

        $contact = FlickrContacts::create([
            'nsid' => '73115043@N07',
        ]);

        $this->artisan('flickr:people-photos')->assertExitCode(0);

        Bus::assertDispatched(FlickrPhotos::class, function ($job) use ($contact) {
            return $job->nsid === $contact->nsid;
        });

        $this->assertEquals(States::STATE_IN_PROGRESS, $contact->fresh()->state_code);
    }
}
