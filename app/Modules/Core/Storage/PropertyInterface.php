<?php

namespace App\Modules\Core\Storage;

use Illuminate\Support\Collection;

interface PropertyInterface
{
    public function getProperty(string $key, $default = null): mixed;

    public function setProperty(string $key, mixed $value): self;

    public function hasProperty(string $key): bool;

    public function removeProperty(string $key): self;

    public function reset(): self;

    public function getProperties(): array;

    public function getStorage(): Collection;
}
