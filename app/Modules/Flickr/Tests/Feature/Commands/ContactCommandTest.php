<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Flickr\Console\ContactCommand;
use App\Modules\Flickr\Jobs\ContactJob;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Bus;

class ContactCommandTest extends TestCase
{
    public function testCommand()
    {
        Bus::fake();

        $this->artisan(ContactCommand::COMMAND)->assertExitCode(0);

        Bus::assertDispatched(ContactJob::class);
    }
}
