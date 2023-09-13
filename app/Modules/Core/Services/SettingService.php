<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Setting;

class SettingService
{
    public function __construct(private Setting $setting)
    {
    }

    public function remember(string $group, string $key, callable $callback): mixed
    {
        if (
            $this->setting->newQuery()
            ->group($group)->key($key)->exists()
        ) {
            return $this->setting->where('group', $group)->where('key', $key)->first()?->value;
        }

        $value = $callback();
        $this->set($group, $key, $value);

        return $value;
    }

    public function set(string $group, string $key, $value): self
    {
        $this->setting->updateOrCreate(compact('group', 'key'), compact('value'));

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

    public function get(string $group, string $key, $default = null)
    {
        return $this->setting->where('group', $group)->where('key', $key)->first()?->value ?? $default;
    }

    public function forget(string $group, string $key): self
    {
        $this->setting->where('group', $group)->where('key', $key)->forceDelete();

        return $this;
    }

    public function getInt(string $group, string $key, $default = null): int
    {
        return (int) $this->get($group, $key, $default);
    }

    public function getArray(string $group, string $key, $default = null): array
    {
        return (array) $this->get($group, $key, $default);
    }
}
