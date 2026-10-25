<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\FloatingIPActions\AssignRequest;
use Boci\HetznerLaravel\Requests\FloatingIPActions\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\FloatingIPActions\ChangeReverseDnsRequest;
use Boci\HetznerLaravel\Requests\FloatingIPActions\GetActionRequest;
use Boci\HetznerLaravel\Requests\FloatingIPActions\ListActionsRequest;
use Boci\HetznerLaravel\Requests\FloatingIPActions\UnassignRequest;
use Boci\HetznerLaravel\Responses\FloatingIPActions\ActionResponse;
use Boci\HetznerLaravel\Responses\FloatingIPActions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Floating IP Actions Fake
 *
 * This fake floating IP actions resource implements ResourceContract for testing purposes.
 * It allows you to mock API responses and assert that specific floating IP action requests
 * were made during testing.
 */
final class FloatingIPActionsFake implements ResourceContract
{
    /**
     * Create a new fake floating IP actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions for a floating IP (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $floatingIpId, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ip_actions',
            'method' => 'list',
            'parameters' => array_merge(['floatingIpId' => $floatingIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($floatingIpId, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['floatingIpId' => $floatingIpId], $parameters));
    }

    /**
     * Get a specific action for a floating IP (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $floatingIpId, string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ip_actions',
            'method' => 'get',
            'parameters' => ['floatingIpId' => $floatingIpId, 'actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionRequest($floatingIpId, $actionId));
        }

        return ActionResponse::fake(['floatingIpId' => $floatingIpId, 'actionId' => $actionId]);
    }

    /**
     * Assign a floating IP to a server (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP
     * @param  array<string, mixed>  $parameters  The assignment parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function assign(string $floatingIpId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ip_actions',
            'method' => 'assign',
            'parameters' => array_merge(['floatingIpId' => $floatingIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AssignRequest($floatingIpId, $parameters));
        }

        return ActionResponse::fake(array_merge(['floatingIpId' => $floatingIpId, 'command' => 'assign_floating_ip'], $parameters));
    }

    /**
     * Unassign a floating IP from a server (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP
     *
     * @throws Throwable When a mock exception is provided
     */
    public function unassign(string $floatingIpId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ip_actions',
            'method' => 'unassign',
            'parameters' => ['floatingIpId' => $floatingIpId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new UnassignRequest($floatingIpId));
        }

        return ActionResponse::fake(['floatingIpId' => $floatingIpId, 'command' => 'unassign_floating_ip']);
    }

    /**
     * Change reverse DNS records for a floating IP (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP
     * @param  array<string, mixed>  $parameters  The reverse DNS parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeReverseDns(string $floatingIpId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ip_actions',
            'method' => 'change_reverse_dns',
            'parameters' => array_merge(['floatingIpId' => $floatingIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeReverseDnsRequest($floatingIpId, $parameters));
        }

        return ActionResponse::fake(array_merge(['floatingIpId' => $floatingIpId, 'command' => 'change_reverse_dns'], $parameters));
    }

    /**
     * Change floating IP protection (fake implementation).
     *
     * @param  string  $floatingIpId  The ID of the floating IP
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $floatingIpId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'floating_ip_actions',
            'method' => 'change_protection',
            'parameters' => array_merge(['floatingIpId' => $floatingIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeProtectionRequest($floatingIpId, $parameters));
        }

        return ActionResponse::fake(array_merge(['floatingIpId' => $floatingIpId, 'command' => 'change_protection'], $parameters));
    }

    /**
     * Assert that a request was sent to the floating IP actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'floating_ip_actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to floating_ip_actions.');
        }
    }

    /**
     * Assert that no requests were sent to the floating IP actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'floating_ip_actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to floating_ip_actions.');
        }
    }
}
