<?php

namespace App\Modules\Flickr\Console;

use App\Modules\Flickr\Jobs\FlickrFavorites;
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
        $contact = \App\Modules\Flickr\Models\FlickrContacts::whereNull('favorites_state_code')
            ->orWhere(function ($query) {
                return $query->where('favorites_state_code', '!=', 'IN_PROGRESS')
                    ->where('favorites_state_code', '!=', 'COMPLETED');
            })
            ->first();

        if (!$contact) {
            \App\Modules\Flickr\Models\FlickrContacts::update([
                'favorites_state_code' => null
            ]);

            return;
        }

        $contact->update([
            'favorites_state_code' => 'IN_PROGRESS'
        ]);

        FlickrFavorites::dispatch($contact->nsid)->onQueue('flickr');
    }
}
