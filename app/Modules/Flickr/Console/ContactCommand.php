<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Flickr\Jobs\ContactJob;
use Illuminate\Console\Command;

class ContactCommand extends Command
{
    public const COMMAND = 'flickr:contacts';
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
    protected $description = 'Fetch all logged user \' contacts.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(IntegrationRepository $repository): void
    {
        $this->info('Fetching contacts...');

        $repository->getCompleted('flickr')->each(function ($integration) {
            $this->output->text('Processing integration: ' . $integration->name);
            ContactJob::dispatch($integration)->onQueue('flickr');
        });
    }
}
