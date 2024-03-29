<?php

namespace App\Modules\Core\Repositories;

use App\Modules\Core\Models\Setting;

class SettingRepository extends BaseRepository
{
    public function __construct()
    {
        $this->setModel(app(Setting::class));
    }

    /**
     * @param string $group
     * @param string $key
     * @return Setting|null
     */
    public function item(string $group, string $key): ?Setting
    {
        /**
         * @phpstan-ignore-next-line
         */
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

    /**
     * @param string $group
     * @param string $key
     * @param $value
     * @return Setting
     */
    public function updateOrCreate(string $group, string $key, $value): Setting
    {
        /**
         * @phpstan-ignore-next-line
         */
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
