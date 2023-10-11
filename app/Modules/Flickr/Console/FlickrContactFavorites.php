<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Core\Services\States;
use App\Modules\Flickr\Jobs\FlickrFavorites;
use App\Modules\Flickr\Repositories\ContactRepository;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;

class FlickrContactFavorites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'flickr:contact-favorites';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all favorite photo of a user.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        app(FlickrService::class)->processContacts();
    }
}
