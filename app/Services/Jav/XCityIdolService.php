<?php

namespace App\Services\Jav;

use App\Jav\Jobs\XCityIdolFetchPages;
use App\Services\Crawler\XCityIdolCrawler;

class XCityIdolService
{
    public const SOURCE = 'xcity_idols';
    public const SOURCE_IDOL = 'xcity_idol';

    public function pages()
    {
        app(XCityIdolCrawler::class)->getSubPages()->each(function ($link) {
            XCityIdolFetchPages::dispatch($link);
        });
    }
}
