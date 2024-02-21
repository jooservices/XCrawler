<?php

namespace App\Modules\Core\Console;

use App\Modules\Core\Services\FileScannerService;
use Illuminate\Console\Command;

class FileScanner extends Command
{
    public const COMMAND = 'jav:file-scanner {path}';

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
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        app(FileScannerService::class)->scan($this->argument('path'));
    }
}
