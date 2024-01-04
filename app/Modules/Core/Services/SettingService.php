<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Repositories\SettingRepository;

class SettingService
{
    public function __construct(private readonly SettingRepository $setting)
    {
    }

    /**
     * @param string $group
     * @param string $key
     * @param callable $callback
     * @return mixed
     */
    public function remember(string $group, string $key, callable $callback): mixed
    {
        if ($this->setting->has($group, $key)) {
            return $this->setting->item($group, $key)->value;
        }

        $value = $callback();
        $this->set($group, $key, $value);

        return $value;
    }

    public function set(string $group, string $key, $value): self
    {
        $this->setting->updateOrCreate($group, $key, $value);

        return $this;
    }

    public function setInt(string $group, string $key, int $value): self
    {
        return $this->set($group, $key, $value);
    }

    public function setArray(string $group, string $key, array $value): self
    {
        return $this->set($group, $key, $value);
    }

    public function forget(string $group, string $key): self
    {
        $this->setting->delete($group, $key);

        return $this;
    }

    public function get(string $group, string $key, $default = null)
    {
        return $this->setting->item($group, $key)?->value ?? $default;
    }

    public function getInt(string $group, string $key, $default = null): int
    {
        return (int)$this->get($group, $key, $default);
    }

    public function getArray(string $group, string $key, $default = null): array
    {
        return (array)$this->get($group, $key, $default);
    }

    public function increment(string $group, string $key, int $value = 1): self
    {
        $this->setting->increment($group, $key, $value);

        return $this;
    }

    public function has(string $group, string $key): bool
    {
        return $this->setting->has($group, $key);
    }
}
