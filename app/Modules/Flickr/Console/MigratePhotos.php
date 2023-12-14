<?php

namespace App\Modules\Flickr\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigratePhotos extends Command
{
    public const COMMAND = 'flickr:migrate-photos';

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
    protected $description = 'Migrate photos from MongoDB to MySQL';

    /**
     * @return void
     */
    public function handle(): void
    {
        $now = Carbon::now();
        DB::connection('mongodb')->table('flickr_photos')->orderBy('created_at')->chunk(10000, function ($photos) use ($now) {
            $photos = collect($photos)->map(function ($photo) use ($now) {
                return [
                    'id' => $photo['id'],
                    'uuid' => Str::orderedUuid(),
                    'owner' => $photo['owner'],
                    'farm' => $photo['farm'] ?? null,
                    'isfamily' => $photo['isfamily'] ?? null,
                    'isfriend' => $photo['isfriend'] ?? null,
                    'ispublic' => $photo['ispublic'] ?? null,
                    'secret' => $photo['secret'] ?? null,
                    'server' => $photo['server'] ?? null,
                    'title' => $photo['title'] ?? null,
                    'sizes' => isset($photo['sizes']) ? json_encode($photo['sizes']) : null,
                    'dateuploaded' => $photo['dateuploaded'] ?? null,
                    'views' => $photo['views'] ?? null,
                    'media' => $photo['media'] ?? null,

                    'created_at' => $photo['created_at'] ? $now : null,
                    'updated_at' => $photo['updated_at'] ? $now : null,
                ];
            });
            $photos = $photos->unique('id');
        });
    }
}
