<?php

namespace App\Modules\JAV\Tests\Feature\Commands\Onejav;

use App\Modules\JAV\Jobs\OnejavCrawlingAll;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CrawlingAllTest extends TestCase
{
    public function testCommand()
    {
        Bus::fake();

        $this->artisan('onejav:crawling-all')
            ->assertExitCode(0);

        Bus::assertDispatched(OnejavCrawlingAll::class, function ($job) {
            return in_array($job->queue,  ['onejav.new', 'onejav.popular']);
        });
    }
}
