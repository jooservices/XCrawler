<?php

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Pool;
use Illuminate\Support\Collection;

class PoolService
{
    public function add(string $job, string $queue = 'default', array $payload = []): Pool
    {
        return Pool::create([
            'job' => $job,
            'queue' => $queue,
            'payload' => $payload,
            'state_code' => States::STATE_INIT,
        ]);
    }

    public function getItems(string $queue, int $limit = 10): Collection
    {
        $items =  Pool::where('queue', $queue)
            ->where('state_code', States::STATE_INIT)
            ->limit($limit)
            ->get();

        Pool::whereIn('_id', $items->pluck('_id'))
            ->update(['state_code' => States::STATE_IN_PROGRESS]);

        return $items;
    }
}
