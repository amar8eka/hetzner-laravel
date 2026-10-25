<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Servers\CreateRequest;
use Boci\HetznerLaravel\Requests\Servers\DeleteRequest;
use Boci\HetznerLaravel\Requests\Servers\ListRequest;
use Boci\HetznerLaravel\Requests\Servers\MetricsRequest;
use Boci\HetznerLaravel\Requests\Servers\RetrieveRequest;
use Boci\HetznerLaravel\Requests\Servers\UpdateRequest;
use Boci\HetznerLaravel\Responses\Servers\CreateResponse;
use Boci\HetznerLaravel\Responses\Servers\DeleteResponse;
use Boci\HetznerLaravel\Responses\Servers\ListResponse;
use Boci\HetznerLaravel\Responses\Servers\MetricsResponse;
use Boci\HetznerLaravel\Responses\Servers\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Servers\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Servers Fake
 *
 * This fake servers resource implements the same interface as the real servers resource for testing purposes.
 * It allows you to mock API responses and assert that specific server requests
 * were made during testing.
 */
final class ServersFake implements ResourceContract
{
    /**
     * Create a new fake servers resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * Create a new server (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The server creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'servers',
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
     * List servers (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'servers',
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

    /**
     * Retrieve a server (fake implementation).
     *
     * @param  string  $serverId  The server ID
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $serverId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'servers',
            'method' => 'retrieve',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($serverId));
        }

        return RetrieveResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Delete a server (fake implementation).
     *
     * @param  string  $serverId  The server ID
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $serverId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'servers',
            'method' => 'delete',
            'parameters' => ['serverId' => $serverId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($serverId));
        }

        return DeleteResponse::fake(['serverId' => $serverId]);
    }

    /**
     * Update a server (fake implementation).
     *
     * @param  string  $serverId  The ID of the server to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $serverId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'servers',
            'method' => 'update',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($serverId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Get server metrics (fake implementation).
     *
     * @param  string  $serverId  The server ID
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function metrics(string $serverId, array $parameters = []): MetricsResponse
    {
        $this->requests[] = [
            'resource' => 'servers',
            'method' => 'metrics',
            'parameters' => array_merge(['serverId' => $serverId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return MetricsResponse::from($response, new MetricsRequest($serverId, $parameters));
        }

        return MetricsResponse::fake(array_merge(['serverId' => $serverId], $parameters));
    }

    /**
     * Get the server actions resource (fake implementation).
     */
    public function actions(): ServerActionsFake
    {
        return new ServerActionsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the servers resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'servers');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to servers.');
        }
    }

    /**
     * Assert that no requests were sent to the servers resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'servers');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to servers.');
        }
    }
}
