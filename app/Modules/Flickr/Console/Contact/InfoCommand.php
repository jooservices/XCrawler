<?php

namespace App\Modules\Flickr\Console\Contact;

use App\Modules\Flickr\Jobs\PeopleInfoJob;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Isolatable;

class InfoCommand extends Command implements Isolatable
{
    public const COMMAND = 'flickr:contact-info {--nsid=}';

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
    protected $description = 'Fetch contact\'s information';

    /**
     * @return void
     */
    public function handle(): void
    {
        $this->info('Fetching contact\' info ...');

        PeopleInfoJob::dispatch($this->option('nsid'))->onQueue(FlickrService::QUEUE_NAME);
    }
}
