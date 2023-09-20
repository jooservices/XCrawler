<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Client\Tests\TestCase;
use App\Modules\Flickr\Jobs\FlickrPhotos;
use App\Modules\Flickr\Models\FlickrContacts;
use Illuminate\Support\Facades\Bus;

class FlickrPeoplePhotosTest extends TestCase
{
    public function testCommand()
    {
        Bus::fake();
        $contact = FlickrContacts::create([
            'nsid' => '73115043@N07',
        ]);

        $this->artisan('flickr:people-photos')
            ->assertExitCode(0);

        Bus::assertDispatched(FlickrPhotos::class);
        $this->assertEquals('IN_PROGRESS', $contact->fresh()->state_code);
    }

    public function testCommandFully()
    {
        $contact = FlickrContacts::create([
            'nsid' => '73115043@N07',
        ]);

        $this->artisan('flickr:people-photos')
            ->assertExitCode(0);

        $this->assertEquals('COMPLETED', $contact->fresh()->state_code);
        $this->assertDatabaseCount('flickr_photos', 507, 'mongodb');
    }
}
