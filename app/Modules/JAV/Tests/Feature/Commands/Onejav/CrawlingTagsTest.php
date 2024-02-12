<?php

namespace App\Modules\JAV\Tests\Feature\Commands\Onejav;

use App\Modules\Core\Facades\Setting;
use App\Modules\JAV\Console\Onejav\TagsCommand;
use App\Modules\JAV\Services\OnejavService;
use App\Modules\JAV\Tests\TestCase;

class CrawlingTagsTest extends TestCase
{
    public function testHandle(): void
    {
        $subpages = Setting::get(OnejavService::SERVICE_NAME, 'subpages', []);
        $this->assertCount(0, $subpages);

        $this->artisan(TagsCommand::COMMAND)
        ->assertExitCode(0);

        $subpages = Setting::get(OnejavService::SERVICE_NAME, 'subpages', []);
        $this->assertCount(350, $subpages);
    }
}
