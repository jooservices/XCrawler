<?php

namespace App\Modules\Flickr\Services\Flickr\Entities;

use App\Modules\Core\Entities\BaseEntity;

class PeopleInfoEntity extends BaseEntity
{
    protected array $fields = [
        'id',
        'nsid',
        'ispro',
        'is_deleted',
        'iconserver',
        'iconfarm',
        'path_alias',
        'has_stats',
        'pro_badge',
        'expire',
        'gender',
        'ignored',
        'contact',
        'friend',
        'family',
        'revcontact',
        'revfriend',
        'revfamily',
        'username',
        'realname',
        'location',
        'description',
        'photos'
    ];

    protected array $casts = [
        'username' => 'string',
        'realname' => 'string',
        'location' => 'string',
        'description' => 'string',
    ];

    public function castingToString($value): string
    {
        if (is_array($value) && isset($value['_content'])) {
            $value = $value['_content'];
        }

        return trim((string)$value);
    }
}
