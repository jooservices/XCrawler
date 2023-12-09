<?php

namespace App\Modules\Core\Storage;

use Illuminate\Support\Collection;

/**
 * General storage class for storing properties.
 */
class Storage implements PropertyInterface, StorageInterface
{
    protected Collection $storage;

    public function __construct(private readonly array $properties = [])
    {
        $this->reset();
    }

    public function getProperty(string $key, $default = null): mixed
    {
        return $this->storage->get($key, $default);
    }

    public function setProperty(string $key, mixed $value): self
    {
        $this->storage->put($key, $value);

        return $this;
    }

    public function hasProperty(string $key): bool
    {
        return $this->storage->has($key);
    }

    public function removeProperty(string $key): self
    {
        $this->storage->forget($key);
        return $this;
    }

    public function reset(): self
    {
        $this->storage = collect($this->properties);

        return $this;
    }

    public function getProperties(): array
    {
        return $this->storage->toArray();
    }

    public function getStorage(): Collection
    {
        return $this->storage;
    }

    public function getStorageId(): string
    {
        return spl_object_hash($this);
    }
}
