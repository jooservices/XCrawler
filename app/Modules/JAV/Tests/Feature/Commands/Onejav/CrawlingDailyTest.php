<?php

namespace App\Modules\JAV\Tests\Feature\Commands\Onejav;

use App\Modules\JAV\Console\Onejav\DailyCommand;
use App\Modules\JAV\Jobs\Onejav\DailyJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CrawlingDailyTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake(DailyJob::class);

        $this->artisan(DailyCommand::class)
            ->assertExitCode(0);

        Queue::assertPushedOn(OnejavService::QUEUE_NAME, DailyJob::class);
    }
}
