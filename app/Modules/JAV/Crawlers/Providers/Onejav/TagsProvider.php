<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav;

use App\Modules\Core\Entities\EntityInterface;
use App\Modules\JAV\Crawlers\AbstractProvider;
use App\Modules\JAV\Crawlers\Providers\Onejav\Entities\ItemsEntity;
use App\Modules\JAV\Crawlers\Providers\Onejav\Entities\TagEntity;
use App\Modules\JAV\Entities\OnejavEntity;
use App\Modules\JAV\Services\OnejavService;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class TagsProvider extends AbstractProvider
{
    protected function parse(Crawler $crawler): EntityInterface
    {
        $item = new ItemsEntity();
        $item->items = collect()->merge(
            $crawler->filter('.card-content .columns a.is-link') ->each(function ($el) {
                $item = new TagEntity();
                $item->url = trim(trim($el->attr('href'), '/'));
                $item->name = trim($el->text(null, false));
                return $item;
            })
        ) ;

        return $item;
    }
}
