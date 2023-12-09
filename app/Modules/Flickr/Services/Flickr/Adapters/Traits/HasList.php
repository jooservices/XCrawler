<?php

namespace App\Modules\Flickr\Services\Flickr\Adapters\Traits;

use App\Modules\Flickr\Exceptions\InvalidRespondException;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;

trait HasList
{
    protected array $listData = [];

    /**
     * @throws GuzzleException
     * @throws InvalidRespondException
     */
    public function getList(array $params = []): Collection
    {
        $params['page'] = $params['page'] ?? 1;
        $response = $this->provider->request(
            $this->getListMethod(),
            array_merge(
                $this->getListParameters(),
                $params
            )
        );

        if (is_string($response->getData())) {
            throw  new InvalidRespondException($response->getBody());
        }

        if (!$response->getData() || !$this->isSuccessfull($response->getData())) {
            return collect();
        }

        $this->listData = $response->getData();

        return collect($response->getData()[$this->getListEntities()][$this->getListEntity()]);
    }

    protected function getListMethod(): string
    {
        return $this->getListMethod;
    }

    protected function getListParameters(): array
    {
        return [
            'per_page' => self::PER_PAGE,
        ];
    }

    protected function getListEntities(): string
    {
        return $this->entities;
    }

    protected function getListEntity(): string
    {
        return $this->entity;
    }

    public function endOfList(): bool
    {
        return
            $this->totalPages() === 0
            || $this->currentPage() === $this->totalPages();
    }

    public function totalPages(): int
    {
        return empty($this->listData)
            ? 0
            : (int)$this->listData[$this->getListEntities()]['pages'];
    }

    public function currentPage(): int
    {
        return empty($this->listData)
            ? 0
            : $this->listData[$this->getListEntities()]['page'] ?? 0;
    }

    public function nextPage(): int
    {
        return $this->currentPage() + 1;
    }

    public function totalItems(): int
    {
        return empty($this->listData)
            ? 0
            : (int)$this->listData[$this->getListEntities()]['total'];
    }
}
