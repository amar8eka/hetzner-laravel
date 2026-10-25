<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\PrimaryIPs\CreateRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPs\DeleteRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPs\ListRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPs\RetrieveRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPs\UpdateRequest;
use Boci\HetznerLaravel\Responses\PrimaryIPs\CreateResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\DeleteResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\ListResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\RetrieveResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPs\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Primary IPs Fake
 *
 * This fake primary IPs resource extends the real primary IPs resource for testing purposes.
 * It allows you to mock API responses and assert that specific primary IP requests
 * were made during testing.
 */
final class PrimaryIPsFake implements ResourceContract
{
    /**
     * Create a new fake primary IPs resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * Create a new primary IP (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The primary IP creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ips',
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
     * List all primary IPs (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ips',
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
     * Get a specific primary IP by ID (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $primaryIpId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ips',
            'method' => 'get',
            'parameters' => ['primaryIpId' => $primaryIpId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($primaryIpId));
        }

        return RetrieveResponse::fake(['primaryIpId' => $primaryIpId]);
    }

    /**
     * Update a primary IP (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $primaryIpId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ips',
            'method' => 'update',
            'parameters' => array_merge(['primaryIpId' => $primaryIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($primaryIpId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['primaryIpId' => $primaryIpId], $parameters));
    }

    /**
     * Delete a primary IP (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $primaryIpId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ips',
            'method' => 'delete',
            'parameters' => ['primaryIpId' => $primaryIpId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($primaryIpId));
        }

        return DeleteResponse::fake(['primaryIpId' => $primaryIpId]);
    }

    /**
     * Get access to Primary IP actions (fake implementation).
     */
    public function actions(): PrimaryIPActionsFake
    {
        return new PrimaryIPActionsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the primary IPs resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'primary_ips');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to primary_ips.');
        }
    }

    /**
     * Assert that no requests were sent to the primary IPs resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'primary_ips');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to primary_ips.');
        }
    }
}
