<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\NetworkActions\AddRouteRequest;
use Boci\HetznerLaravel\Requests\NetworkActions\AddSubnetRequest;
use Boci\HetznerLaravel\Requests\NetworkActions\ChangeIpRangeRequest;
use Boci\HetznerLaravel\Requests\NetworkActions\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\NetworkActions\DeleteRouteRequest;
use Boci\HetznerLaravel\Requests\NetworkActions\DeleteSubnetRequest;
use Boci\HetznerLaravel\Requests\NetworkActions\GetActionRequest;
use Boci\HetznerLaravel\Requests\NetworkActions\ListActionsRequest;
use Boci\HetznerLaravel\Responses\NetworkActions\ActionResponse;
use Boci\HetznerLaravel\Responses\NetworkActions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Network Actions Fake
 *
 * This fake network actions resource implements ResourceContract for testing purposes.
 * It allows you to mock API responses and assert that specific network action requests
 * were made during testing.
 */
final class NetworkActionsFake implements ResourceContract
{
    /**
     * Create a new fake network actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions for a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $networkId, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'network_actions',
            'method' => 'list',
            'parameters' => array_merge(['networkId' => $networkId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($networkId, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['networkId' => $networkId], $parameters));
    }

    /**
     * Get a specific action for a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $networkId, string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'network_actions',
            'method' => 'get',
            'parameters' => ['networkId' => $networkId, 'actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionRequest($networkId, $actionId));
        }

        return ActionResponse::fake(['networkId' => $networkId, 'actionId' => $actionId]);
    }

    /**
     * Add a route to a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  array<string, mixed>  $parameters  The route parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function addRoute(string $networkId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'network_actions',
            'method' => 'add_route',
            'parameters' => array_merge(['networkId' => $networkId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AddRouteRequest($networkId, $parameters));
        }

        return ActionResponse::fake(array_merge(['networkId' => $networkId, 'command' => 'add_route'], $parameters));
    }

    /**
     * Add a subnet to a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  array<string, mixed>  $parameters  The subnet parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function addSubnet(string $networkId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'network_actions',
            'method' => 'add_subnet',
            'parameters' => array_merge(['networkId' => $networkId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AddSubnetRequest($networkId, $parameters));
        }

        return ActionResponse::fake(array_merge(['networkId' => $networkId, 'command' => 'add_subnet'], $parameters));
    }

    /**
     * Change the IP range of a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  array<string, mixed>  $parameters  The IP range parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeIpRange(string $networkId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'network_actions',
            'method' => 'change_ip_range',
            'parameters' => array_merge(['networkId' => $networkId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeIpRangeRequest($networkId, $parameters));
        }

        return ActionResponse::fake(array_merge(['networkId' => $networkId, 'command' => 'change_ip_range'], $parameters));
    }

    /**
     * Change protection settings for a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $networkId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'network_actions',
            'method' => 'change_protection',
            'parameters' => array_merge(['networkId' => $networkId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeProtectionRequest($networkId, $parameters));
        }

        return ActionResponse::fake(array_merge(['networkId' => $networkId, 'command' => 'change_protection'], $parameters));
    }

    /**
     * Delete a route from a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  string  $routeId  The ID of the route to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function deleteRoute(string $networkId, string $routeId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'network_actions',
            'method' => 'delete_route',
            'parameters' => ['networkId' => $networkId, 'routeId' => $routeId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DeleteRouteRequest($networkId, $routeId));
        }

        return ActionResponse::fake(['networkId' => $networkId, 'routeId' => $routeId, 'command' => 'delete_route']);
    }

    /**
     * Delete a subnet from a network (fake implementation).
     *
     * @param  string  $networkId  The ID of the network
     * @param  string  $subnetId  The ID of the subnet to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function deleteSubnet(string $networkId, string $subnetId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'network_actions',
            'method' => 'delete_subnet',
            'parameters' => ['networkId' => $networkId, 'subnetId' => $subnetId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new DeleteSubnetRequest($networkId, $subnetId));
        }

        return ActionResponse::fake(['networkId' => $networkId, 'subnetId' => $subnetId, 'command' => 'delete_subnet']);
    }

    /**
     * Assert that a request was sent to the network actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'network_actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to network_actions.');
        }
    }

    /**
     * Assert that no requests were sent to the network actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'network_actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to network_actions.');
        }
    }
}
