<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Client\Exceptions\NoIntegrateException;
use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Flickr\Jobs\ContactsJob;
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
     * @param IntegrationRepository $repository
     * @return void
     * @throws NoIntegrateException
     */
    public function handle(IntegrationRepository $repository): void
    {
        $this->info('Fetching contacts...');

        $integration = $repository->getPrimary(FlickrService::SERVICE_NAME);
        $this->output->text('Processing integration: ' . $integration->name);

        ContactsJob::dispatch($integration)->onQueue(FlickrService::QUEUE_NAME);
    }
}
