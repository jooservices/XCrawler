<?php

namespace App\Modules\Core\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Routing\ResponseFactory;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class ApiController extends Controller
{
    use Traits\ErrorsResponding;
    use Traits\GeneralResponding;

    protected array $data = [];

    protected array $messages = [];

    protected int $status;

    protected array $headers = [];

    protected ResponseFactory $response;

    public function __construct(ResponseFactory $response)
    {
        $this->status = HttpResponse::HTTP_OK;
        $this->response = $response;
    }

    /**
     * Return the response
     *
     * @return JsonResponse
     */
    protected function respond(): JsonResponse
    {
        $response = [
            'status' => empty($this->messages),
            'data' => $this->getData(),
        ];

        if (!empty($this->messages)) {
            $response['message'] = reset($this->messages);
            $response['errors'] = $this->messages;
        }

        return $this->response->json(
            $response,
            $this->getStatusCode(),
            $this->getHeaders()
        );
    }

    /**
     * Get the data
     *
     * @return mixed
     */
    protected function getData()
    {
        return $this->data;
    }

    /**
     * Set the data
     *
     * @param mixed $data
     *
     * @return $this
     */
    protected function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the Status Code
     *
     * @return int
     */
    protected function getStatusCode(): int
    {
        return $this->status;
    }

    /**
     * Get the headers
     *
     * @return array
     */
    protected function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Set the headers
     *
     * @param array $headers
     *
     * @return $this
     */
    protected function setHeaders(array $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * Set the Status Code
     *
     * @param int $status
     *
     * @return $this
     */
    protected function setStatusCode(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Set the error message as data
     *
     * @param string $message
     *
     * @return $this
     */
    protected function setErrorMessage(string $message): self
    {
        $this->messages[] = $message;

        return $this;
    }
}
