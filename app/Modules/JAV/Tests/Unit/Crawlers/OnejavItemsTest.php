<?php

namespace App\Modules\JAV\Tests\Unit\Crawlers;

use App\Modules\JAV\Crawlers\Providers\Onejav\OnejavItems;
use Tests\TestCase;

class OnejavItemsTest extends TestCase
{
    public function testGetItems()
    {
        $provider = app(OnejavItems::class);
        dd($provider->crawl('https://onejav.com/new?page=4'));
    }
}
