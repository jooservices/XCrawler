<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Models\Setting;

class SettingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(app(Setting::class));
    }

    public function item(string $group, string $key): ?Setting
    {
        return $this->getModel()->newQuery()
            ->where('group', $group)
            ->where('key', $key)
            ->first();
    }

    public function has(string $group, string $key): bool
    {
        return $this->getModel()->newQuery()
            ->where('group', $group)
            ->where('key', $key)
            ->exists();
    }

    public function updateOrCreate(string $group, string $key, $value): Setting
    {
        return $this->getModel()->newQuery()
            ->updateOrCreate(compact('group', 'key'), compact('value'));
    }

    public function delete(string $group, string $key): bool
    {
        return $this->getModel()->newQuery()
            ->where('group', $group)
            ->where('key', $key)
            ->forceDelete();
    }

    public function increment(string $group, string $key, int $amount = 1): int
    {
        return $this->getModel()->newQuery()
            ->where('group', $group)
            ->where('key', $key)
            ->increment('value', $amount);
    }
}
