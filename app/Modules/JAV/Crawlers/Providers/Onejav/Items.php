<?php

namespace App\Modules\JAV\Crawlers\Providers\Onejav;

use App\Modules\Client\Responses\XResponse;
use App\Modules\Client\Responses\XResponseInterface;
use App\Modules\JAV\Crawlers\Providers\AbstractProvider;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class Items extends AbstractProvider
{
    public const DEFAULT_DATE_FORMAT = 'Y/m/d';
    public const ONEJAV_URL = 'https://onejav.com';

    protected ?XResponseInterface $response;

    protected string $url;

    /**
     * @throws GuzzleException
     */
    public function crawl(string $url, array $data = [], string $method = 'GET'): Collection
    {
        $this->url = $url;
        $this->response = $this->client->request($method, $url, $data);

        if (! $this->isSuccess($this->response)) {
            return $this->items;
        }

        $dom = new Crawler($this->response->getData());
        $pageNode = $dom->filter('a.pagination-link')->last();
        $this->lastPage = $pageNode->count() === 0 ? 1 : (int) $pageNode->text();

        $this->items = $this->items->merge(
            $dom->filter('.container .columns')
                ->each(function ($el) {
                    return $this->parse($el);
                })
        );

        return $this->items;
    }

    protected function isSuccess(?XResponse $response): bool
    {
        return $response !== null && $response->isSuccessful() && $response->getData() !== null;
    }

    private function parse(Crawler $crawler): Entity
    {
        $item = new Entity();

        if ($crawler->filter('h5.title a')->count()) {
            $item->url = self::ONEJAV_URL . trim($crawler->filter('h5.title a')->attr('href'));
        }

        if ($crawler->filter('.columns img.image')->count()) {
            $item->cover = trim($crawler->filter('.columns img.image')->attr('src'));
        }

        if ($crawler->filter('h5 a')->count()) {
            $item->dvd_id = (trim($crawler->filter('h5 a')->text(null, false)));
            $item->dvd_id = implode(
                '-',
                preg_split('/(,?\\s+)|((?<=[a-z])(?=\\d))|((?<=\\d)(?=[a-z]))/i', $item->dvd_id)
            );
        }

        if ($crawler->filter('h5 span')->count()) {
            $size = trim($crawler->filter('h5 span')->text(null, false));

            if (str_contains($size, 'MB')) {
                $item->size = (float) trim(str_replace('MB', '', $size));
                $item->size /= 1024;
            } elseif (str_contains($size, 'GB')) {
                $item->size = (float) trim(str_replace('GB', '', $size));
            }

            $item->size = round($item->size, 2);
        }

        // Always use href because it'll never change but text will be
        $item->date = $this->convertStringToDateTime(
            trim($crawler->filter('.subtitle.is-6 a')->attr('href'))
        );

        $item->genres = collect($crawler->filter('.tags .tag')->each(
            function ($genres) {
                return trim($genres->text(null, false));
            }
        ))->reject(function ($value) {
            return empty($value);
        })->unique()->toArray();

        // Description
        $description = $crawler->filter('.level.has-text-grey-dark');
        $item->description = $description->count() ? trim($description->text(null, false)) : null;
        $item->description = preg_replace("/\r|\n/", '', $item->description);

        $item->performers = collect($crawler->filter('.panel .panel-block')->each(
            function ($performers) {
                return trim($performers->text(null, false));
            }
        ))->reject(function ($value) {
            return empty($value);
        })->unique()->toArray();

        $item->torrent = self::ONEJAV_URL . trim($crawler->filter('.control.is-expanded a')->attr('href'));

        // Gallery. Only for FC
        $gallery = $crawler->filter('.columns .column a img');
        if ($gallery->count()) {
            $item->gallery = collect($gallery->each(
                function ($image) {
                    return trim($image->attr('src'));
                }
            ))->reject(function ($value) {
                return empty($value);
            })->unique()->toArray();
        }

        return $item;
    }

    private function convertStringToDateTime(string $date): ?Carbon
    {
        $date = Carbon::createFromFormat(self::DEFAULT_DATE_FORMAT, trim($date, '/'));

        if ($date === false) {
            return null;
        }

        return $date;
    }
}
