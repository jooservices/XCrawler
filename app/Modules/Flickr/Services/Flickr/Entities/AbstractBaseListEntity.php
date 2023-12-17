<?php

namespace App\Modules\Flickr\Services\Flickr\Entities;

use App\Modules\Core\Entity\BaseEntity;
use Illuminate\Support\Collection;
use InvalidArgumentException;

abstract class AbstractBaseListEntity extends BaseEntity
{
    public function __construct(protected array $data)
    {
        parent::__construct($data);

        if (empty($this->data[$this->getEntities()])) {
            throw new InvalidArgumentException('Invalid data');
        }
    }

    abstract public function getEntities(): string;

    abstract public function getEntity(): string;

    public function getItems(): Collection
    {
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
        return $this->getPage() === $this->getPages();
    }

    public function getNextPage(): int
    {
        return $this->getPage() + 1;
    }
}
