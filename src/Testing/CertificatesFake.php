<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Certificates\CreateRequest;
use Boci\HetznerLaravel\Requests\Certificates\DeleteRequest;
use Boci\HetznerLaravel\Requests\Certificates\ListRequest;
use Boci\HetznerLaravel\Requests\Certificates\RetrieveRequest;
use Boci\HetznerLaravel\Requests\Certificates\UpdateRequest;
use Boci\HetznerLaravel\Responses\Certificates\CreateResponse;
use Boci\HetznerLaravel\Responses\Certificates\DeleteResponse;
use Boci\HetznerLaravel\Responses\Certificates\ListResponse;
use Boci\HetznerLaravel\Responses\Certificates\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Certificates\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

final class CertificatesFake implements ResourceContract
{
    /**
     * @param  array<int, ResponseInterface|Throwable>  $responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'certificates',
            'method' => 'create',
            'parameters' => $parameters,
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return CreateResponse::from($response, new CreateRequest($parameters));
        }

        return CreateResponse::fake($parameters);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'certificates',
            'method' => 'list',
            'parameters' => $parameters,
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListResponse::from($response, new ListRequest($parameters));
        }

        return ListResponse::fake($parameters);
    }

    public function retrieve(string $certificateId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'certificates',
            'method' => 'retrieve',
            'parameters' => ['certificateId' => $certificateId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($certificateId));
        }

        return RetrieveResponse::fake(['certificateId' => $certificateId]);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public function update(string $certificateId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'certificates',
            'method' => 'update',
            'parameters' => array_merge(['certificateId' => $certificateId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($certificateId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['certificateId' => $certificateId], $parameters));
    }

    public function delete(string $certificateId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'certificates',
            'method' => 'delete',
            'parameters' => ['certificateId' => $certificateId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($certificateId));
        }

        return DeleteResponse::fake(['certificateId' => $certificateId]);
    }

    /**
     * Assert that a request was sent to the certificates resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'certificates');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to certificates.');
        }
    }

    /**
     * Assert that no requests were sent to the certificates resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'certificates');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to certificates.');
        }
    }
}
