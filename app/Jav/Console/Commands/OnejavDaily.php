<?php

namespace App\Jav\Console\Commands;

use App\Services\Jav\OnejavService;
use Illuminate\Console\Command;

class OnejavDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jav:onejav-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Onejav - Daily';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        app(OnejavService::class)->daily();
    }
}
