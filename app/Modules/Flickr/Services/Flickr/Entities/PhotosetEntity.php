<?php

namespace App\Modules\Flickr\Services\Flickr\Entities;

use App\Modules\Core\Entities\BaseEntity;

class PhotosetEntity extends BaseEntity
{
    protected array $fields = [
        'id',
        'owner',
        'username',
        'primary',
        'secret',
        'server',
        'farm',
        'title',
        'description',
        'photos'
    ];

    protected array $casts = [
        'title' => 'string',
        'description' => 'string'
    ];

    public function castingToString($value): string
    {
        if (is_array($value) && isset($value['_content'])) {
            $value = $value['_content'];
        }

        return trim((string)$value);
    }
}
