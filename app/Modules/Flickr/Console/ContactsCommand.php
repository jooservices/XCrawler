<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\ContactJob;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;

class ContactsCommand extends Command
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

        $integration = $repository->getItem('flickr', null, States::STATE_COMPLETED);
        $this->output->text('Processing integration: ' . $integration->name);

        ContactJob::dispatch($integration)->onQueue(FlickrService::QUEUE_NAME);
    }
}
