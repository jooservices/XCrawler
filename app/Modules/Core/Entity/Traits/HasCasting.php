<?php

namespace App\Modules\Core\Entity\Traits;

use Illuminate\Support\Collection;

trait HasCasting
{
    protected array $casts = [];

    public function toInt($value): int
    {
        return (int) $value;
    }

    public function toFloat($value): float
    {
        return (float) $value;
    }

    public function toBool($value): bool
    {
        return (bool) $value;
    }

    public function toArray($value): array
    {
        return (array) $value;
    }

    public function toObject($value): object
    {
        return (object) $value;
    }

    public function toJson($value): string
    {
        return json_encode($value);
    }

    public function toCollection($value): Collection
    {
        return $value instanceof Collection ? $value : collect($value);
    }
}
