<?php

namespace App\Modules\Core\Http\Controllers\Traits;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

trait ErrorsResponding
{
    abstract protected function setData($data): self;
    abstract protected function getData();

    abstract protected function setStatusCode(int $status): self;
    abstract protected function respond(): JsonResponse;

    abstract protected function setErrorMessage(string $message): self;

    /**
     * Respond Bad Request - 400
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondBadRequest(string $message = 'Bad Request'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_BAD_REQUEST)
            ->respond();
    }

    /**
     * Respond Unauthorized - 401
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondUnauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_UNAUTHORIZED)
            ->respond();
    }

    /**
     * Respond Forbidden - 403
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondForbidden(string $message = 'Forbidden'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_FORBIDDEN)
            ->respond();
    }

    /**
     * Respond Not Found - 404
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondNotFound(string $message = 'Not Found'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_NOT_FOUND)
            ->respond();
    }

    /**
     * Respond Expectation Failed - 417
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondExpectationFailed(string $message = 'Expectation Failed'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_EXPECTATION_FAILED)
            ->respond();
    }

    /**
     * Respond Unprocessable Entity - 422
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondUnprocessableEntity(string $message = 'Unprocessable Entity'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_UNPROCESSABLE_ENTITY)
            ->respond();
    }

    /**
     * Respond Internal Server Error - 500
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondInternalServerError(string $message = 'Internal Server Error'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_INTERNAL_SERVER_ERROR)
            ->respond();
    }

    /**
     * Respond Conflict - 409
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondConflict(string $message = 'Resource Conflict'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_CONFLICT)
            ->respond();
    }

    /**
     * Respond Service Not Available - 503
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public function respondServiceNotAvailable(string $message = 'Service Not Available'): JsonResponse
    {
        return $this->setErrorMessage($message)
            ->setStatusCode(HttpResponse::HTTP_SERVICE_UNAVAILABLE)
            ->respond();
    }
}
