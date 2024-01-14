<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Flickr\Console\ContactsCommand;
use App\Modules\Flickr\Jobs\ContactsJob;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Queue;

class ContactsCommandTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake(ContactsJob::class);

        $this->artisan(ContactsCommand::COMMAND)->assertExitCode(0);

        Queue::assertPushed(ContactsJob::class, function ($job) {
            return $job->integration->is($this->integration);
        });
    }
}
