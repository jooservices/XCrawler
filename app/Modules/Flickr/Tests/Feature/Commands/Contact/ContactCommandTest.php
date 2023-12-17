<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Contact;

use App\Modules\Flickr\Console\ContactsCommand;
use App\Modules\Flickr\Jobs\ContactJob;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Bus;

class ContactCommandTest extends TestCase
{
    public function testCommand()
    {
        Bus::fake();

        $this->artisan(ContactsCommand::COMMAND)->assertExitCode(0);

        Bus::assertDispatched(ContactJob::class);
    }
}
