<?php

namespace App\Modules\JAV\Transformers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property string uuid
 * @property string dvd_id
 * @property string url
 * @property string cover
 * @property string torrent
 * @property float size
 * @property array gallery
 */
class MovieResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'uuid' => $this->uuid,
            'dvd_id' => $this->dvd_id,
            'url' => $this->url,
            'cover' => !config('jav.censored', true) ? $this->cover : 'https://placehold.co/600x400?font=roboto',
            'torrent' => $this->torrent,
            'size' => $this->size,
            'gallery' => $this->gallery,
        ];
    }
}
