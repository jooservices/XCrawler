<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav;

use App\Modules\Core\Entities\EntityInterface;
use App\Modules\JAV\Crawlers\AbstractProvider;
use App\Modules\JAV\Crawlers\Providers\Onejav\Entities\ItemsEntity;
use App\Modules\JAV\Entities\OnejavEntity;
use App\Modules\JAV\Services\OnejavService;
use Carbon\Carbon;
use Symfony\Component\DomCrawler\Crawler;

class ItemsProvider extends AbstractProvider
{
    protected function parse(Crawler $crawler): EntityInterface
    {
        $item = new ItemsEntity();
        $pageNode = $crawler->filter('a.pagination-link')->last();

        $item->lastPage = $pageNode->count() === 0 ? 1 : (int)$pageNode->text();
        $item->items = collect()->merge(
            $crawler->filter('.container .columns')
                ->each(function ($el) {
                    $item = new OnejavEntity();
                    if ($el->filter('h5.title a')->count()) {
                        $item->url = OnejavService::ONEJAV_URL . trim($el->filter('h5.title a')->attr('href'));
                    }

                    if ($el->filter('.columns img.image')->count()) {
                        $item->cover = $el->filter('.columns img.image')->attr('src');
                    }

                    if ($el->filter('h5 a')->count()) {
                        $item->dvd_id = trim($el->filter('h5 a')->text(null, false));
                        $item->dvd_id = implode(
                            '-',
                            preg_split('/(,?\\s+)|((?<=[a-z])(?=\\d))|((?<=\\d)(?=[a-z]))/i', $item->dvd_id)
                        );
                    }

                    if ($el->filter('h5 span')->count()) {
                        $size = $el->filter('h5 span')->text(null, false);

                        if (str_contains($size, 'MB')) {
                            $item->size = (float)trim(str_replace('MB', '', $size));
                            $item->size /= 1024;
                        } elseif (str_contains($size, 'GB')) {
                            $item->size = (float)trim(str_replace('GB', '', $size));
                        }

                        $item->size = round($item->size, 2);
                    }

                    // Always use href because it'll never change but text will be
                    $item->date = $this->convertStringToDateTime(
                        trim($el->filter('.subtitle.is-6 a')->attr('href'))
                    );

                    $item->genres = collect($el->filter('.tags .tag')->each(
                        function ($genres) {
                            return trim($genres->text(null, false));
                        }
                    ))->reject(function ($value) {
                        return empty($value);
                    })->unique()->toArray();

                    // Description
                    $item->description = $this->description($el);
                    $item->performers = $this->performers($el);

                    $item->torrent = OnejavService::ONEJAV_URL . trim($el->filter('.control.is-expanded a')->attr('href'));

                    // Gallery. Only for FC
                    $item->gallery = $this->gallery($el);

                    return $item;
                })
        );

        return $item;
    }

    private function description(Crawler $el): ?string
    {
        $description = $el->filter('.level.has-text-grey-dark');
        if ($description->count() === 0) {
            return null;
        }

        return trim(preg_replace("/\r|\n/", '', $description->text(null, false)));
    }

    private function performers(Crawler $el): array
    {
        $performers = $el->filter('.panel .panel-block');
        if ($performers->count() === 0) {
            return [];
        }

        return collect($performers->each(
            function ($performers) {
                return trim($performers->text(null, false));
            }
        ))->reject(function ($value) {
            return empty($value);
        })->unique()->toArray();
    }

    private function gallery(Crawler $el)
    {
        $gallery = $el->filter('.columns .column a img');
        if ($gallery->count() === 0) {
            return null;
        }

        return collect($gallery->each(
            function ($image) {
                return trim($image->attr('src'));
            }
        ))->reject(function ($value) {
            return empty($value);
        })->unique()->toArray();
    }

    private function convertStringToDateTime(string $date): ?Carbon
    {
        $date = Carbon::createFromFormat(OnejavService::DEFAULT_DATE_FORMAT, trim($date, '/'));

        if ($date === false) {
            return null;
        }

        return $date;
    }
}
