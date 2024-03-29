<?php

namespace App\Modules\Core\Traits;

use Illuminate\Support\Collection;

trait HasProperties
{
    protected Collection $properties;

    public function bootHasProperties(): void
    {
        $this->properties = collect();
    }

    public function __get(string $name): mixed
    {
        return $this->getProperty($name);
    }

    public function __set(string $name, mixed $value)
    {
        $this->setProperty($name, $value);
    }

    public function getProperty(string $key, mixed $default = null): mixed
    {
        return $this->properties->get($key, $default);
    }

    public function setProperty(string $key, mixed $value): void
    {
        $value = is_string($value) ? trim($value) : $value;
        $this->properties->put($key, $value);
    }

    public function getProperties(): array
    {
        return $this->properties->toArray();
    }

    public function setProperties(array $properties): void
    {
        foreach ($properties as $key => $value) {
            $this->setProperty($key, $value);
        }
    }

    public function hasProperty(string $key): bool
    {
        return $this->properties->has($key);
    }
}
