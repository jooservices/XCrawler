<?php

namespace App\Modules\Core\Entities\Traits;

use Illuminate\Support\Collection;

trait HasCasting
{
    protected array $casts = [];

    public function castingToString($value): string
    {
        return (string)$value;
    }

    public function castingToInt($value): int
    {
        return (int)$value;
    }

    public function castingToFloat($value): float
    {
        return (float)$value;
    }

    public function castingToBool($value): bool
    {
        return (bool)$value;
    }

    public function castingToArray($value): array
    {
        if (is_string($value)) {
            return json_decode($value, true);
        }

        return (array)$value;
    }

    public function castingToObject($value): object
    {
        return (object)$value;
    }

    public function castingToJson($value): string
    {
        return json_encode($value);
    }

    public function castingToCollection($value): Collection
    {
        return $value instanceof Collection ? $value : collect($value);
    }
}
