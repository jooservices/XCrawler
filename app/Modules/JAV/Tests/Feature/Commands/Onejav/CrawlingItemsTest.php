<?php

namespace App\Modules\JAV\Tests\Feature\Commands\Onejav;

use App\Modules\JAV\Jobs\Onejav\ItemsJob;
use App\Modules\JAV\Services\OnejavService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CrawlingItemsTest extends TestCase
{
    public function testCommand()
    {
        Queue::fake(ItemsJob::class);

        $url = $this->faker->url;
        $this->artisan('onejav:items ' . $url)
            ->assertExitCode(0);

        Queue::assertPushedOn(OnejavService::QUEUE_NAME, ItemsJob::class);
    }
}
