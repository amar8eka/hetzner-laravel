<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\PrimaryIPActions\AssignRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPActions\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPActions\ChangeReverseDnsRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPActions\GetActionByIdRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPActions\GetActionRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPActions\ListActionsRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPActions\ListAllActionsRequest;
use Boci\HetznerLaravel\Requests\PrimaryIPActions\UnassignRequest;
use Boci\HetznerLaravel\Responses\PrimaryIPActions\ActionResponse;
use Boci\HetznerLaravel\Responses\PrimaryIPActions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Primary IP Actions Fake
 *
 * This fake primary IP actions resource implements ResourceContract for testing purposes.
 * It allows you to mock API responses and assert that specific primary IP action requests
 * were made during testing.
 */
final class PrimaryIPActionsFake implements ResourceContract
{
    /**
     * Create a new fake primary IP actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions (general) (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function listAll(array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ip_actions',
            'method' => 'list_all',
            'parameters' => $parameters,
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListAllActionsRequest($parameters));
        }

        return ListActionsResponse::fake($parameters);
    }

    /**
     * List all actions for a primary IP (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $primaryIpId, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ip_actions',
            'method' => 'list',
            'parameters' => array_merge(['primaryIpId' => $primaryIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($primaryIpId, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['primaryIpId' => $primaryIpId], $parameters));
    }

    /**
     * Get a specific action by ID (general) (fake implementation).
     *
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function getById(string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ip_actions',
            'method' => 'get_by_id',
            'parameters' => ['actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionByIdRequest($actionId));
        }

        return ActionResponse::fake(['actionId' => $actionId]);
    }

    /**
     * Get a specific action for a primary IP (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $primaryIpId, string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ip_actions',
            'method' => 'get',
            'parameters' => ['primaryIpId' => $primaryIpId, 'actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionRequest($primaryIpId, $actionId));
        }

        return ActionResponse::fake(['primaryIpId' => $primaryIpId, 'actionId' => $actionId]);
    }

    /**
     * Assign a primary IP to a resource (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP
     * @param  array<string, mixed>  $parameters  The assignment parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function assign(string $primaryIpId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ip_actions',
            'method' => 'assign',
            'parameters' => array_merge(['primaryIpId' => $primaryIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new AssignRequest($primaryIpId, $parameters));
        }

        return ActionResponse::fake(array_merge(['primaryIpId' => $primaryIpId, 'command' => 'assign_primary_ip'], $parameters));
    }

    /**
     * Change reverse DNS records for a primary IP (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP
     * @param  array<string, mixed>  $parameters  The reverse DNS parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeReverseDns(string $primaryIpId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ip_actions',
            'method' => 'change_reverse_dns',
            'parameters' => array_merge(['primaryIpId' => $primaryIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeReverseDnsRequest($primaryIpId, $parameters));
        }

        return ActionResponse::fake(array_merge(['primaryIpId' => $primaryIpId, 'command' => 'change_reverse_dns'], $parameters));
    }

    /**
     * Change primary IP protection (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $primaryIpId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ip_actions',
            'method' => 'change_protection',
            'parameters' => array_merge(['primaryIpId' => $primaryIpId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeProtectionRequest($primaryIpId, $parameters));
        }

        return ActionResponse::fake(array_merge(['primaryIpId' => $primaryIpId, 'command' => 'change_protection'], $parameters));
    }

    /**
     * Unassign a primary IP from a resource (fake implementation).
     *
     * @param  string  $primaryIpId  The ID of the primary IP
     *
     * @throws Throwable When a mock exception is provided
     */
    public function unassign(string $primaryIpId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'primary_ip_actions',
            'method' => 'unassign',
            'parameters' => ['primaryIpId' => $primaryIpId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new UnassignRequest($primaryIpId));
        }

        return ActionResponse::fake(['primaryIpId' => $primaryIpId, 'command' => 'unassign_primary_ip']);
    }

    /**
     * Assert that a request was sent to the primary IP actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'primary_ip_actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to primary_ip_actions.');
        }
    }

    /**
     * Assert that no requests were sent to the primary IP actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'primary_ip_actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to primary_ip_actions.');
        }
    }
}
