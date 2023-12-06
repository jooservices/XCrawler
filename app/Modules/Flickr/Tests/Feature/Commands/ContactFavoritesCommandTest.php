<?php

namespace App\Modules\Flickr\Tests\Feature\Commands;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Console\Contact\FavoritesCommand;
use App\Modules\Flickr\Jobs\ContactFavoritesJob;
use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\FlickrService;
use App\Modules\Flickr\Tests\TestCase;
use Illuminate\Support\Facades\Queue;

class ContactFavoritesCommandTest extends TestCase
{
    public function testHandle()
    {
        Queue::fake();

        $contact = FlickrContact::factory()->create();
        $task = $contact->tasks()->create([
            'task' => FlickrService::TASK_CONTACT_FAVORITES,
            'state_code' => States::STATE_INIT
        ]);

        $this->artisan(FavoritesCommand::COMMAND)->assertExitCode(0);
        Queue::assertPushed(ContactFavoritesJob::class, function ($job) use ($contact) {
            return $job->nsid === $contact->nsid;
        });

        $this->assertDatabaseMissing('tasks', [
            'id' => $task->id,
        ], 'mongodb');

        $newTask = $contact->tasks()->where(
            'task',
            FlickrService::TASK_CONTACT_FAVORITES
        )->first();

        $this->assertFalse($newTask->is($task));
    }
}
