<?php

namespace App\Modules\JAV\Tests\Feature\Commands\Onejav;

use App\Modules\JAV\Console\Onejav\AllCommand;
use App\Modules\JAV\Jobs\Onejav\AllJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CrawlingAllTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake(AllJob::class);

        $this->artisan('onejav:all')
            ->assertExitCode(0);

        Queue::assertPushedOn(OnejavService::QUEUE_NAME, AllJob::class);
    }
}
