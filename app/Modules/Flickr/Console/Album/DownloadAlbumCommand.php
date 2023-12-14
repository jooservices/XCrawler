<?php

namespace App\Modules\Flickr\Console\Album;

use App\Modules\Client\Repositories\IntegrationRepository;
use App\Modules\Flickr\Jobs\ContactJob;
use App\Modules\Flickr\Models\FlickrPhotoset;
use App\Modules\Flickr\Services\FlickrService;
use Illuminate\Console\Command;

class DownloadAlbumCommand extends Command
{
    public const COMMAND = 'flickr:dowload-album';
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
    protected $description = 'Download all photos\'s album.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(IntegrationRepository $repository): void
    {
        $albumId = $this->ask('Enter album id: ');

        $album =FlickrPhotoset::firstOrCreate(
            ['id' => $albumId],
            ['state' => FlickrPhotoset::STATE_INIT]
        );
    }
}
