<?php

namespace App\Modules\Flickr\Repositories;

use App\Modules\Core\Repositories\CrudRepository;
use App\Modules\Core\Services\States;
use App\Modules\Flickr\Models\FlickrContacts;
use Illuminate\Support\Collection;

class ContactRepository extends CrudRepository
{
    public function __construct()
    {
        $this->setModel(app(FlickrContacts::class));
    }

    public function create(array $attributes): FlickrContacts
    {
        return FlickrContacts::updateOrCreate(
            [
                'nsid' => $attributes['nsid']
            ],
            $attributes
        );
    }

    public function getContactsForPhotos(int $limit = 1): Collection
    {
        $items = $this->getModel()->newQuery()
            ->whereNull('state_code')
            ->orWhere(function ($query) {
                return $query->where('state_code', '!=', States::STATE_IN_PROGRESS)
                    ->where('state_code', '!=', States::STATE_COMPLETED);
            })
            ->limit($limit)
            ->get();

        if ($this->getModel()->newQuery()->count() > 1 && $items->isEmpty()) {
            FlickrContacts::query()->update([
                'state_code' => null
            ]);

            return $this->getContactsForPhotos($limit);
        }

        return $items;
    }

    public function getContactForFavorites(int $limit = 1): Collection
    {
        $items = $this->getModel()->newQuery()
            ->whereNull('favorites_state_code')
            ->orWhere(function ($query) {
                return $query->where('favorites_state_code', '!=', States::STATE_IN_PROGRESS)
                    ->where('favorites_state_code', '!=', States::STATE_COMPLETED);
            })
            ->limit($limit)
            ->get();

        if ($this->getModel()->newQuery()->count() > 1 && $items->isEmpty()) {
            FlickrContacts::query()->update([
                'favorites_state_code' => null
            ]);

            return $this->getContactForFavorites($limit);
        }

        return $items;
    }
}
