<?php

namespace App\Modules\JAV\Tests\Feature\Commands\Onejav;

use App\Modules\JAV\Jobs\OnejavCrawlingDaily;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CrawlingDailyTest extends TestCase
{
    public function testCommand()
    {
        Bus::fake();

        $this->artisan('onejav:crawling-daily')
            ->assertExitCode(0);

        Bus::assertDispatched(OnejavCrawlingDaily::class, function ($job) {
            return $job->queue === 'onejav';
        });
    }
}
