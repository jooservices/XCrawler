<?php

namespace App\Modules\Core\Models;

use App\Modules\Core\Models\Traits\HasUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * @property string $group
 * @property string $key
 * @property mixed $value
 */
class Setting extends Model
{
    use HasUuid;
    use SoftDeletes;

    protected $connection = 'mongodb';
    protected $collection = 'settings';

    protected $fillable = [
        'group',
        'key',
        'value',
        'is_private',
    ];

    public function scopeGroup($query, $group)
    {
        return $query->where('group', $group);
    }

    public function scopeKey($query, $key)
    {
        return $query->where('key', $key);
    }
}
