<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Firewalls\CreateRequest;
use Boci\HetznerLaravel\Requests\Firewalls\DeleteRequest;
use Boci\HetznerLaravel\Requests\Firewalls\ListRequest;
use Boci\HetznerLaravel\Requests\Firewalls\RetrieveRequest;
use Boci\HetznerLaravel\Requests\Firewalls\UpdateRequest;
use Boci\HetznerLaravel\Responses\Firewalls\CreateResponse;
use Boci\HetznerLaravel\Responses\Firewalls\DeleteResponse;
use Boci\HetznerLaravel\Responses\Firewalls\ListResponse;
use Boci\HetznerLaravel\Responses\Firewalls\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Firewalls\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Firewalls Fake
 *
 * This fake firewalls resource extends the real firewalls resource for testing purposes.
 * It allows you to mock API responses and assert that specific firewall requests
 * were made during testing.
 */
final class FirewallsFake implements ResourceContract
{
    /**
     * Create a new fake firewalls resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * Create a new firewall (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The firewall creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'firewalls',
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
     * List all firewalls (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'firewalls',
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
     * Get a specific firewall by ID (fake implementation).
     *
     * @param  string  $firewallId  The ID of the firewall to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $firewallId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'firewalls',
            'method' => 'get',
            'parameters' => ['firewallId' => $firewallId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($firewallId));
        }

        return RetrieveResponse::fake(['firewallId' => $firewallId]);
    }

    /**
     * Update a firewall (fake implementation).
     *
     * @param  string  $firewallId  The ID of the firewall to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $firewallId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'firewalls',
            'method' => 'update',
            'parameters' => array_merge(['firewallId' => $firewallId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($firewallId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['firewallId' => $firewallId], $parameters));
    }

    /**
     * Delete a firewall (fake implementation).
     *
     * @param  string  $firewallId  The ID of the firewall to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $firewallId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'firewalls',
            'method' => 'delete',
            'parameters' => ['firewallId' => $firewallId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($firewallId));
        }

        return DeleteResponse::fake(['firewallId' => $firewallId]);
    }

    /**
     * Get access to Firewall actions (fake implementation).
     */
    public function actions(): FirewallActionsFake
    {
        return new FirewallActionsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the firewalls resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'firewalls');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to firewalls.');
        }
    }

    /**
     * Assert that no requests were sent to the firewalls resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'firewalls');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to firewalls.');
        }
    }
}
