<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\LoadBalancers\CreateRequest;
use Boci\HetznerLaravel\Requests\LoadBalancers\DeleteRequest;
use Boci\HetznerLaravel\Requests\LoadBalancers\ListRequest;
use Boci\HetznerLaravel\Requests\LoadBalancers\MetricsRequest;
use Boci\HetznerLaravel\Requests\LoadBalancers\RetrieveRequest;
use Boci\HetznerLaravel\Requests\LoadBalancers\UpdateRequest;
use Boci\HetznerLaravel\Responses\LoadBalancers\CreateResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\DeleteResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\ListResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\MetricsResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\RetrieveResponse;
use Boci\HetznerLaravel\Responses\LoadBalancers\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Load Balancers Fake
 *
 * This fake load balancers resource extends the real load balancers resource for testing purposes.
 * It allows you to mock API responses and assert that specific load balancer requests
 * were made during testing.
 */
final class LoadBalancersFake implements ResourceContract
{
    /**
     * Create a new fake load balancers resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * Create a new load balancer (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The load balancer creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancers',
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
     * List all load balancers (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancers',
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
     * Get a specific load balancer by ID (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $loadBalancerId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancers',
            'method' => 'get',
            'parameters' => ['loadBalancerId' => $loadBalancerId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($loadBalancerId));
        }

        return RetrieveResponse::fake(['loadBalancerId' => $loadBalancerId]);
    }

    /**
     * Update a load balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $loadBalancerId, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancers',
            'method' => 'update',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($loadBalancerId, $parameters));
        }

        return UpdateResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters));
    }

    /**
     * Delete a load balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $loadBalancerId): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancers',
            'method' => 'delete',
            'parameters' => ['loadBalancerId' => $loadBalancerId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($loadBalancerId));
        }

        return DeleteResponse::fake(['loadBalancerId' => $loadBalancerId]);
    }

    /**
     * Get metrics for a Load Balancer (fake implementation).
     *
     * @param  string  $loadBalancerId  The ID of the load balancer
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function metrics(string $loadBalancerId, array $parameters = []): MetricsResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancers',
            'method' => 'metrics',
            'parameters' => array_merge(['loadBalancerId' => $loadBalancerId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return MetricsResponse::from($response, new MetricsRequest($loadBalancerId, $parameters));
        }

        return MetricsResponse::fake(array_merge(['loadBalancerId' => $loadBalancerId], $parameters));
    }

    /**
     * Get access to Load Balancer actions (fake implementation).
     */
    public function actions(): LoadBalancerActionsFake
    {
        return new LoadBalancerActionsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the load balancers resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'load_balancers');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to load_balancers.');
        }
    }

    /**
     * Assert that no requests were sent to the load balancers resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'load_balancers');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to load_balancers.');
        }
    }
}
