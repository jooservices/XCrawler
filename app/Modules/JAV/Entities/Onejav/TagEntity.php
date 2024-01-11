<?php

namespace App\Modules\JAV\Entities\Onejav;

use App\Modules\Core\Entities\BaseEntity;

/**
 * @property string $url
 * @property string $name
 */
class TagEntity extends BaseEntity
{
    protected array $fields = [
        'url',
        'name'
    ];

    protected array $casts = [
        'url' => 'string',
        'name' => 'string'
    ];
}
