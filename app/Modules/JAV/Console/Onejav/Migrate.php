<?php

namespace App\Modules\JAV\Console\Onejav;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Connection;

class Migrate extends Command
{
    public const COMMAND = 'onejav:migrate';

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
    protected $description = 'Migrate Onejav from MongoDB to MySQL.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->output->text('Migrating Onejav from MongoDB to MySQL...');
        $this->output->progressStart();

        /**
         * @var Connection $connection
         */
        $connection = DB::connection('mongodb');
        $connection
            ->collection('onejav')
            ->cursor()
            ->each(function ($document) {
                DB::table('onejav')->updateOrInsert([
                    'url' => $document['url'],
                    'dvd_id' => $document['dvd_id'],
                ], [
                    'uuid' => Str::orderedUuid(),
                    'cover' => $document['cover'] ?? null,
                    'size' => $document['size'],
                    'date' => $document['date']->toDateTime()->format('Y-m-d'),
                    'genres' => $document['genres'] ?? null,
                    'description' => $document['description'] ?? null,
                    'performers' => $document['performers'] ?? null,
                    'torrent' => $document['torrent'],
                    'gallery' => $document['gallery'] ?? null,
                    'created_at' => $document['created_at']->toDateTime()->format('Y-m-d'),
                    'updated_at' => $document['updated_at']->toDateTime()->format('Y-m-d'),
                ]);
                $this->output->progressAdvance();
            });

        $this->output->progressFinish();
    }
}
