<?php

namespace App\Modules\Flickr\Console;

use Illuminate\Console\Command;

class FlickrContacts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'flickr:contacts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all logged user \' contacts.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        \App\Modules\Flickr\Jobs\FlickrContacts::dispatch()->onQueue('flickr');
    }
}
