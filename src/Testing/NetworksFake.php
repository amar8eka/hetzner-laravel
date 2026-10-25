<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Networks\CreateRequest;
use Boci\HetznerLaravel\Requests\Networks\DeleteRequest;
use Boci\HetznerLaravel\Requests\Networks\ListRequest;
use Boci\HetznerLaravel\Requests\Networks\MembersRequest;
use Boci\HetznerLaravel\Requests\Networks\RetrieveRequest;
use Boci\HetznerLaravel\Requests\Networks\UpdateRequest;
use Boci\HetznerLaravel\Responses\Networks\CreateResponse;
use Boci\HetznerLaravel\Responses\Networks\DeleteResponse;
use Boci\HetznerLaravel\Responses\Networks\ListResponse;
use Boci\HetznerLaravel\Responses\Networks\MembersResponse;
use Boci\HetznerLaravel\Responses\Networks\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Networks\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Networks Fake
 *
 * This fake networks resource extends the real networks resource for testing purposes.
 * It allows you to mock API responses and assert that specific network requests
 * were made during testing.
 */
final class NetworksFake implements ResourceContract
{
    /**
     * Create a new fake networks resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * Create a new network (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The network creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'networks',
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
     * List all networks (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'networks',
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
     * Get a specific network by ID (fake implementation).
     *
     * @param  string  $networkId  The ID of the network to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $networkId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'networks',
            'method' => 'get',
            'parameters' => ['networkId' => $networkId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($networkId));
        }

        return RetrieveResponse::fake(['networkId' => $networkId]);
    }

    /**
     * List the members of a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function members(string $networkId, array $parameters = []): MembersResponse
    {
        $this->requests[] = [
            'resource' => 'networks',
            'method' => 'members',
            'parameters' => array_merge(['networkId' => $networkId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return MembersResponse::from($response, new MembersRequest($networkId, $parameters));
        }

        return MembersResponse::fake(['networkId' => $networkId]);
    }

    /**
     * Update a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $networkId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'networks',
            'method' => 'update',
            'parameters' => array_merge(['networkId' => $networkId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($networkId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['networkId' => $networkId], $parameters));
    }

    /**
     * Delete a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $networkId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'networks',
            'method' => 'delete',
            'parameters' => ['networkId' => $networkId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($networkId));
        }

        return DeleteResponse::fake(['networkId' => $networkId]);
    }

    /**
     * Get access to network actions
     */
    public function actions(): NetworkActionsFake
    {
        return new NetworkActionsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the networks resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'networks');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to networks.');
        }
    }

    /**
     * Assert that no requests were sent to the networks resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'networks');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to networks.');
        }
    }
}
