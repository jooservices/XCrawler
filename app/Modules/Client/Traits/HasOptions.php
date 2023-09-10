<?php

namespace App\Modules\Client\Traits;

use App\Modules\Client\Services\Factory;
use Illuminate\Support\Collection;

trait HasOptions
{
    protected Collection $options;

    public function bootHasOptions(): void
    {
        $this->options = collect();
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return HasOptions|Factory
     */
    public function addOptions(array $options): self
    {
        $this->options->merge($options);

        return $this;
    }

    public function getOptions(): Collection
    {
        return $this->options;
    }

    /**
     * @phpstan-ignore-next-line
     *
     * @return HasOptions|Factory
     */
    public function setOptions(array $options): self
    {
        $this->options = collect($options);

        return $this;
    }

    public function addOption(string $key, mixed $value): self
    {
        $this->options->put($key, $value);

        return $this;
    }

    public function getOption(string $key, mixed $default = null): mixed
    {
        return $this->options->get($key, $default);
    }

    public function hasOption(string $key): bool
    {
        return $this->options->has($key);
    }

    public function removeOption(string $key): self
    {
        $this->options->forget($key);

        return $this;
    }

    /**
     * @phpstan-ignore-next-line
     * @return array
     */
    public function getOptionsArray(): array
    {
        return $this->options->toArray();
    }
}
