<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Flickr\Models\FlickrContact;
use App\Modules\Flickr\Services\TaskService;
use Illuminate\Console\Command;

class SyncContactTasksCommand extends Command
{
    public const COMMAND = 'flickr:contact-tasks';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create contacts \' tasks.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->info('Fetching contacts...');

        FlickrContact::whereDoesntHave('tasks')->cursor()->each(function (FlickrContact $contact) {
            $this->output->text("Creating tasks for contact {$contact->nsid}...");
            foreach (TaskService::CONTACT_TASKS as $task) {
                $contact->tasks()->create([
                    'task' => $task,
                ]);
            }
        });
    }
}
