<?php

namespace App\Modules\Core\Facades;

use App\Modules\Core\Services\SettingService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed remember(string $group, string $key, $default = null)
 */
class Setting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SettingService::class;
    }
}
