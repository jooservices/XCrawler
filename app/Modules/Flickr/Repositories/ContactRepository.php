<?php

namespace App\Modules\Flickr\Repositories;

use App\Modules\Core\Repositories\CrudRepository;
use App\Modules\Flickr\Models\FlickrContact;
use Illuminate\Support\Collection;

class ContactRepository extends CrudRepository
{
    public function __construct()
    {
        $this->setModel(app(FlickrContact::class));
    }

    public function create(array $attributes): FlickrContact
    {
        return FlickrContact::updateOrCreate(
            [
                'nsid' => $attributes['nsid']
            ],
            $attributes
        );
    }

    public function getContactsForTask(string $task, int $limit = 1): Collection
    {
        return $this->getModel()->newQuery()
            ->whereDoesntHave('tasks', function ($query) use ($task) {
                return $query->where('task', $task);
            })
            ->limit($limit)
            ->get();
    }
}
