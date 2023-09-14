<?php

namespace App\Modules\Core\Models\Traits;

use Illuminate\Support\Str;

/**
 * @property string $uuid
 */
trait HasUuid
{
    public static function bootHasUuid()
    {
        static::creating(function ($model) {
            $model->{$model->getUuidName()} = Str::orderedUuid()->toString();
        });
    }

    public function getUuidName()
    {
        return property_exists($this, 'uuidName') ? $this->uuidName : 'uuid';
    }

    public function initializeHasUuid()
    {
        $this->mergeCasts([$this->getUuidName() => 'string']);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function scopeUuid($query, $uuid)
    {
        return $query->where($this->getUuidName(), $uuid);
    }
}
