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
        $mysqlConnection = DB::connection('mysql');
        foreach (DB::connection('mongodb')->table('flickr_contacts')->cursor() as $contact) {
            $this->output->writeln('Processing contact ' . $contact['nsid']);
            $mysqlConnection->table('flickr_contacts')->insert($this->mapping($contact));
        }
    }

    private function mapping(array $contact): array
    {
        $now = Carbon::now();
        return [
            'uuid' => Str::orderedUuid(),
            'nsid' => $contact['nsid'],
            'username' => $contact['username'] ?? null,
            'realname' => $contact['realname'] ?? null,
            'friend' => $contact['friend'] ?? null,
            'family' => $contact['family'] ?? null,
            'ignored' => $contact['ignored'] ?? null,
            'rev_ignored' => $contact['rev_ignored'] ?? null,
            'iconserver' => $contact['iconserver'] ?? null,
            'iconfarm' => $contact['iconfarm'] ?? null,
            'path_alias' => $contact['path_alias'] ?? null,
            'location' => $contact['location'] ?? null,
            'description' => $contact['description'] ?? null,
            'photosurl' => $contact['photosurl'] ?? null,
            'profileurl' => $contact['profileurl'] ?? null,
            'mobileurl' => $contact['mobileurl'] ?? null,

            'created_at' => $contact['created_at'] ? $now : null,
            'updated_at' => $contact['updated_at'] ? $now : null,
        ];
    }
}
