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
     * @return void
     */
    public function handle(): void
    {
        $contact = \App\Modules\Flickr\Models\FlickrContacts::whereNull('state_code')
            ->orWhere(function ($query) {
                return $query->where('state_code', '!=', 'IN_PROGRESS')
                    ->where('state_code', '!=', 'COMPLETED');
            })
            ->first();

        if (!$contact) {
            /**
             * @phpstan-ignore-next-line
             */
            \App\Modules\Flickr\Models\FlickrContacts::update([
                'state_code' => null
            ]);

            return;
        }

        $contact->update([
            'state_code' => 'IN_PROGRESS'
        ]);

        FlickrPhotos::dispatch($contact->nsid)->onQueue('flickr');
    }
}
