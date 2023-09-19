<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Client\Tests\TestCase;
use App\Modules\Flickr\Jobs\FlickrContacts;
use Illuminate\Support\Facades\Bus;

class FlickrContactsTest extends TestCase
{
    public function testCommand()
    {
        Bus::fake();

        $this->artisan('flickr:contacts')
            ->assertExitCode(0);

        Bus::assertDispatched(FlickrContacts::class);
    }
}
