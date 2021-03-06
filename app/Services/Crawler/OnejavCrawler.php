<?php

namespace App\Services\Crawler;

use App\Models\Onejav;
use App\Services\Client\XCrawlerClient;
use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Support\Collection;
use Symfony\Component\DomCrawler\Crawler;

class OnejavCrawler
{
    private XCrawlerClient $client;

    public function __construct()
    {
        $this->client = app(XCrawlerClient::class);
        $this->client->init('onejav');
    }

    public function getItems(string $url, array $payload = []): Collection
    {
        $response = $this->client->get($url, $payload);

        if ($response->isSuccessful()) {
            return collect($response->getData()->filter('.container .columns')->each(function ($el) {
                return $this->parse($el);
            }));
        }

        return collect();
    }

    public function daily(&$page = 1): Collection
    {
        $items = collect();
        $page = $this->getItemsRecursive($items, Onejav::HOMEPAGE_URL . '/' . Carbon::now()->format('Y/m/d'));

        return $items;
    }

    public function popular(): Collection
    {
        $items = collect();
        $this->getItemsRecursive($items, Onejav::HOMEPAGE_URL . '/popular');

        return $items;
    }

    public function search(string $keyword, string $by = 'search')
    {
        $items = collect();
        $this->getItemsRecursive($items, Onejav::HOMEPAGE_URL . '/' . $by . '/' . urlencode($keyword));

        return $items;
    }

    public function tag(string $tag): Collection
    {
        return $this->search($tag, strtolower(__FUNCTION__));
    }

    public function actress(string $name): Collection
    {
        return $this->search($name, strtolower(__FUNCTION__));
    }

    public function getItemsRecursive(Collection &$items, string $url, array $payload = []): int
    {
        $currentPage = $payload['page'] ?? 1;
        $response = $this->client->get($url, $payload);

        if ($response->isSuccessful()) {
            $pageNode = $response->getData()->filter('a.pagination-link')->last();
            $lastPage = $pageNode->count() === 0 ? 1 : (int)$pageNode->text();

            $items = $items->merge(collect($response->getData()->filter('.container .columns')->each(function ($el) {
                return $this->parse($el);
            })));

            if (empty($payload) || $payload['page'] < $lastPage) {
                $lastPage = $this->getItemsRecursive($items, $url, ['page' => $currentPage + 1]);
            }

            return $lastPage;
        }

        return 1;
    }

    private function parse(Crawler $crawler): Item
    {
        $item = app(Item::class);
        $item->url = Onejav::HOMEPAGE_URL . trim($crawler->filter('h5.title a')->attr('href'));

        if ($crawler->filter('.columns img.image')->count()) {
            $item->cover = trim($crawler->filter('.columns img.image')->attr('src'));
        }

        if ($crawler->filter('h5 a')->count()) {
            $item->dvd_id = (trim($crawler->filter('h5 a')->text(null, false)));
            $item->dvd_id = implode('-', preg_split("/(,?\s+)|((?<=[a-z])(?=\d))|((?<=\d)(?=[a-z]))/i", $item->dvd_id));
        }

        if ($crawler->filter('h5 span')->count()) {
            $item->size = trim($crawler->filter('h5 span')->text(null, false));

            if (str_contains($item->size, 'MB')) {
                $item->size = (float)trim(str_replace('MB', '', $item->size));
                $item->size /= 1024;
            } elseif (str_contains($item->size, 'GB')) {
                $item->size = (float)trim(str_replace('GB', '', $item->size));
            }
        }

        $item->date = $this->convertStringToDateTime(trim($crawler->filter('.subtitle.is-6 a')->attr('href')));
        $item->tags = collect($crawler->filter('.tags .tag')->each(
            function ($tag) {
                return trim($tag->text(null, false));
            }
        ))->reject(function ($value) {
            return null === $value || empty($value);
        })->unique()->toArray();

        $description = $crawler->filter('.level.has-text-grey-dark');
        $item->description = $description->count() ? trim($description->text(null, false)) : null;
        $item->description = preg_replace("/\r|\n/", '', $item->description);

        $item->actresses = collect($crawler->filter('.panel .panel-block')->each(
            function ($actress) {
                return trim($actress->text(null, false));
            }
        ))->reject(function ($value) {
            return null === $value || empty($value);
        })->unique()->toArray();

        $item->torrent = Onejav::HOMEPAGE_URL . trim($crawler->filter('.control.is-expanded a')->attr('href'));

        return $item;
    }

    private function convertStringToDateTime(string $date): ?DateTime
    {
        try {
            $date = trim($date, '/');
            if (!$dateTime = DateTime::createFromFormat('Y/m/j', $date)) {
                return null;
            }

            return $dateTime;
        } catch (Exception) {
            return null;
        }
    }
}
