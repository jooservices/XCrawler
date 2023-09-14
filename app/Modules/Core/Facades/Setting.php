<?php

namespace App\Modules\Core\Facades;

use App\Modules\Core\Services\SettingService;
use Illuminate\Support\Facades\Facade;

class Setting extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SettingService::class;
    }
}
