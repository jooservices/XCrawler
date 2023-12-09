<?php

namespace App\Modules\Client\Console\Integration;

use App\Modules\Client\Models\Integration;
use App\Modules\Core\Services\States;
use Illuminate\Console\Command;

class AddCommand extends Command
{
    public const COMMAND = 'client:integration-add';

    protected $signature = self::COMMAND;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add integration';

    public function handle(): void
    {
        $service = $this->ask('Enter service name: ');
        $name = $this->ask('Enter name: ');
        $key = $this->ask('Enter key: ');
        $secret = $this->ask('Enter secret: ');
        $callback = $this->ask('Enter callback: ');
        $isPrimary = $this->ask('Is primary? (y/n): ');

        Integration::create([
            'service' => $service,
            'name' => $name,
            'key' => $key,
            'secret' => $secret,
            'callback' => $callback,
            'is_primary' => $isPrimary === 'y',
            'state_code' => States::STATE_INIT,
        ]);
    }
}
