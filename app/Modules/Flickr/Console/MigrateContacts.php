<?php

namespace App\Modules\Flickr\Console;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MigrateContacts extends Command
{
    public const COMMAND = 'flickr:migrate-contacts';

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
    protected $description = 'Migrate contacts from MongoDB to MySQL';

    /**
     * @return void
     */
    public function handle(): void
    {
        $now = Carbon::now();
        $items = DB::connection('mongodb')->table('flickr_contacts')->limit(1000)->get();
        $items = $items->map(function ($item) use ($now) {
            return [
                'uuid' => Str::orderedUuid(),
                'nsid' => $item['nsid'],
                'username' => $item['username'] ?? null,
                'realname' => $item['realname'] ?? null,
                'friend' => $item['friend'] ?? null,
                'family' => $item['family'] ?? null,
                'ignored' => $item['ignored'] ?? null,
                'rev_ignored' => $item['rev_ignored'] ?? null,
                'iconserver' => $item['iconserver'] ?? null,
                'iconfarm' => $item['iconfarm'] ?? null,
                'path_alias' => $item['path_alias'] ?? null,
                'location' => $item['location'] ?? null,
                'description' => $item['description'] ?? null,
                'photosurl' => $item['photosurl'] ?? null,
                'profileurl' => $item['profileurl'] ?? null,
                'mobileurl' => $item['mobileurl'] ?? null,

                'created_at' => $item['created_at'] ? $now : null,
                'updated_at' => $item['updated_at'] ? $now : null,
            ];
        });

        $items = $items->unique('nsid');
        DB::connection('mysql')->table('flickr_contacts')->insert($items->toArray());
    }
}
