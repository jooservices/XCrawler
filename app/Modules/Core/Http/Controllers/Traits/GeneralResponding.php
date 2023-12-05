<?php

namespace App\Modules\Core\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

trait GeneralResponding
{
    abstract protected function setData($data): self;

    abstract protected function getData();

    abstract protected function setStatusCode(int $status): self;

    abstract protected function respond(): JsonResponse;


    /**
     * Respond Created - 201
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function respondCreated($data = []): JsonResponse
    {
        return $this->setData($data)
            ->setStatusCode(HttpResponse::HTTP_CREATED)
            ->respond();
    }

    /**
     * Respond Accepted - 202
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function respondAccepted($data = []): JsonResponse
    {
        return $this->setData($data)
            ->setStatusCode(HttpResponse::HTTP_ACCEPTED)
            ->respond();
    }

    /**
     * Respond No Content - 204
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function respondNoContent($data = []): JsonResponse
    {
        return $this->setData($data)
            ->setStatusCode(HttpResponse::HTTP_NO_CONTENT)
            ->respond();
    }


    public function respondDeleted(): JsonResponse
    {
        return $this
            ->setStatusCode(HttpResponse::HTTP_NO_CONTENT)
            ->respond();
    }

    /**
     * Respond Ok - 200
     *
     * @param mixed $data
     *
     * @return JsonResponse
     */
    public function respondOk($data = []): JsonResponse
    {
        return $this->setData($data)
            ->setStatusCode(HttpResponse::HTTP_OK)
            ->respond();
    }
}
