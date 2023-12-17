<?php

namespace App\Modules\Flickr\Tests\Feature\Commands\Contact;

use App\Modules\Flickr\Console\ContactsCommand;
use App\Modules\Flickr\Jobs\ContactsJob;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Bus;

class ContactsCommandTest extends TestCase
{
    public function testCommand()
    {
        Bus::fake();

        $this->artisan(ContactsCommand::COMMAND)->assertExitCode(0);

        Bus::assertDispatched(ContactsJob::class);
    }
}
