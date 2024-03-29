<?php

namespace App\Modules\JAV\Http\Controllers;

use App\Modules\Core\Http\Controllers\ResourceController;
use App\Modules\JAV\Http\Requests\GetMovieIndexRequest;
use App\Modules\JAV\Services\Movie\MovieService;
use App\Modules\JAV\Transformers\GenreResource;
use App\Modules\JAV\Transformers\IdolResource;
use App\Modules\JAV\Transformers\MovieResource;

class JAVController extends ResourceController
{
    public function index(GetMovieIndexRequest $request, MovieService $service)
    {
        $items = $service->pagination(collect($request->validated()));
        $resource = MovieResource::collection($items);

        return $this->respondOk(
            [
                'items' => $resource->resolve(),
                'total' => $items->total(),
                'perPage' => $items->perPage(),
                'is_last_page' => !$items->hasMorePages(),
            ]
        );
    }

    public function genres(MovieService $service)
    {
        $resource = GenreResource::collection($service->genres());

        return $this->respondOk(
            [
                'items' => $resource->resolve(),
            ]
        );
    }

    public function idols(MovieService $service)
    {
        $resource = IdolResource::collection($service->genres());

        return $this->respondOk(
            [
                'items' => $resource->resolve(),
            ]
        );
    }
}
