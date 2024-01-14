<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\TaskService;
use App\Modules\Flickr\Tests\TestCase;

class SyncContactTaskCommandTest extends TestCase
{
    public function testSync()
    {
        $contact = FlickrContact::factory()->create();

        $this->artisan('flickr:contact-tasks')
            ->assertExitCode(0);

        /**
         * Sync again will not make duplicate
         */
        $this->assertEquals(count(TaskService::CONTACT_TASKS), $contact->tasks()->count());
        $this->artisan('flickr:contact-tasks')
            ->assertExitCode(0);
        $this->assertEquals(count(TaskService::CONTACT_TASKS), $contact->refresh()->tasks()->count());
    }
}
