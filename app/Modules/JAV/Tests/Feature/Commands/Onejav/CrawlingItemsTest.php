<?php

namespace App\Modules\JAV\Tests\Feature\Commands\Onejav;

use App\Modules\JAV\Jobs\OnejavCrawlingItems;
use Illuminate\Support\Facades\Bus;
use Tests\TestCase;

class CrawlingItemsTest extends TestCase
{
    public function testCommand()
    {
        Bus::fake();

        $this->artisan('onejav:crawling-items ' . $this->faker->url);

        Bus::assertDispatched(OnejavCrawlingItems::class, function ($job) {
            return $job->queue === 'onejav';
        });
    }
}
