<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Flickr\Jobs\FlickrPhotos;
use Illuminate\Console\Command;

class FlickrPeoplePhotos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'flickr:people-photos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch all photos of a user.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(): void
    {
        $contact = \App\Modules\Flickr\Models\FlickrContacts::whereNull('state_code')
            ->orWhere('state_code', '!=', 'COMPLETED')
            ->first();

        $contact->update([
            'state_code' => 'IN_PROGRESS'
        ]);

        FlickrPhotos::dispatch($contact->nsid);
    }
}
