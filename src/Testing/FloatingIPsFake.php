<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\FloatingIPs\CreateRequest;
use Boci\HetznerLaravel\Requests\FloatingIPs\DeleteRequest;
use Boci\HetznerLaravel\Requests\FloatingIPs\ListRequest;
use Boci\HetznerLaravel\Requests\FloatingIPs\RetrieveRequest;
use Boci\HetznerLaravel\Requests\FloatingIPs\UpdateRequest;
use Boci\HetznerLaravel\Responses\FloatingIPs\CreateResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\DeleteResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\ListResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\RetrieveResponse;
use Boci\HetznerLaravel\Responses\FloatingIPs\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Floating IPs Fake
 *
 * This fake floating IPs resource extends the real floating IPs resource for testing purposes.
 * It allows you to mock API responses and assert that specific floating IP requests
 * were made during testing.
 */
final class FloatingIPsFake implements ResourceContract
{
    /**
     * Create a new fake floating IPs resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * Create a new floating IP (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The floating IP creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ips',
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
     * List all floating IPs (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ips',
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
     * Get a specific floating IP by ID (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $floatingIpId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ips',
            'method' => 'get',
            'parameters' => ['floatingIpId' => $floatingIpId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($floatingIpId));
        }

        return RetrieveResponse::fake(['floatingIpId' => $floatingIpId]);
    }

    /**
     * Update a floating IP (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $floatingIpId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ips',
            'method' => 'update',
            'parameters' => array_merge(['floatingIpId' => $floatingIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($floatingIpId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['floatingIpId' => $floatingIpId], $parameters));
    }

    /**
     * Delete a floating IP (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $floatingIpId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ips',
            'method' => 'delete',
            'parameters' => ['floatingIpId' => $floatingIpId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($floatingIpId));
        }

        return DeleteResponse::fake(['floatingIpId' => $floatingIpId]);
    }

    /**
     * Get access to Floating IP actions (fake implementation).
     */
    public function actions(): FloatingIPActionsFake
    {
        return new FloatingIPActionsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the floating IPs resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'floating_ips');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to floating_ips.');
        }
    }

    /**
     * Assert that no requests were sent to the floating IPs resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'floating_ips');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to floating_ips.');
        }
    }
}
