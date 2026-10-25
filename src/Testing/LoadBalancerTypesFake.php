<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\LoadBalancerTypes\ListRequest;
use Boci\HetznerLaravel\Requests\LoadBalancerTypes\RetrieveRequest;
use Boci\HetznerLaravel\Responses\LoadBalancerTypes\ListResponse;
use Boci\HetznerLaravel\Responses\LoadBalancerTypes\RetrieveResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Load Balancer Types Fake
 *
 * This fake load balancer types resource extends the real load balancer types resource for testing purposes.
 * It allows you to mock API responses and assert that specific load balancer type requests
 * were made during testing.
 */
final class LoadBalancerTypesFake implements ResourceContract
{
    /**
     * Create a new fake load balancer types resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all load balancer types (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_types',
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
     * Get a specific load balancer type by ID (fake implementation).
     *
     * @param  string  $loadBalancerTypeId  The ID of the load balancer type to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $loadBalancerTypeId): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'load_balancer_types',
            'method' => 'get',
            'parameters' => ['loadBalancerTypeId' => $loadBalancerTypeId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($loadBalancerTypeId));
        }

        return RetrieveResponse::fake(['loadBalancerTypeId' => $loadBalancerTypeId]);
    }

    /**
     * Assert that a request was sent to the load balancer types resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'load_balancer_types');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to load_balancer_types.');
        }
    }

    /**
     * Assert that no requests were sent to the load balancer types resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'load_balancer_types');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to load_balancer_types.');
        }
    }
}
