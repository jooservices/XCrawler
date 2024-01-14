<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav;

use App\Modules\JAV\Crawlers\AbstractProvider;
use App\Modules\JAV\Entities\Onejav\TagEntity;
use App\Modules\JAV\Entities\Onejav\TagsEntity;
use Symfony\Component\DomCrawler\Crawler;

class TagsProvider extends AbstractProvider
{
    protected function parse(Crawler $crawler): TagsEntity
    {
        $item = new TagsEntity();
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
