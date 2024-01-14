<?php

namespace App\Modules\Flickr\Services\Flickr\Entities;

use App\Modules\Core\Entities\BaseEntity;
use App\Modules\Flickr\Exceptions\FlickrRespondedException\MissingEntityElement;
use Illuminate\Support\Collection;

abstract class AbstractBaseListEntity extends BaseEntity
{
    /**
     * @throws MissingEntityElement
     */
    public function __construct(protected array $data)
    {
        parent::__construct($data);

        if (
            empty($this->data[$this->getEntities()])
        ) {
            throw new MissingEntityElement(
                sprintf(
                    'Missing element "%s" in response',
                    $this->getEntities()
                )
            );
        }
    }

    abstract public function getEntities(): string;

    abstract public function getEntity(): string;

    public function getItems(): Collection
    {
        if ($this->getTotal() === 0) {
            return collect();
        }

        return collect($this->data[$this->getEntities()][$this->getEntity()]);
    }

    public function getPage(): int
    {
        return (int)$this->data[$this->getEntities()]['page'];
    }

    public function getPages(): int
    {
        return (int)$this->data[$this->getEntities()]['pages'];
    }

    public function getTotal(): int
    {
        return (int)$this->data[$this->getEntities()]['total'];
    }

    public function getPerPage(): int
    {
        return (int)$this->data[$this->getEntities()]['perpage'];
    }

    public function isCompleted(): bool
    {
        return $this->getPage() === $this->getPages()
            || $this->getTotal() === 0;
    }

    public function getNextPage(): int
    {
        return $this->getPage() + 1;
    }
}
