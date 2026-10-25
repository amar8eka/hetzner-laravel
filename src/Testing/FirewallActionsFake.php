<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\FirewallActions\ApplyToResourcesRequest;
use Boci\HetznerLaravel\Requests\FirewallActions\GetActionRequest;
use Boci\HetznerLaravel\Requests\FirewallActions\ListActionsRequest;
use Boci\HetznerLaravel\Requests\FirewallActions\RemoveFromResourcesRequest;
use Boci\HetznerLaravel\Requests\FirewallActions\SetRulesRequest;
use Boci\HetznerLaravel\Responses\FirewallActions\ActionResponse;
use Boci\HetznerLaravel\Responses\FirewallActions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Firewall Actions Fake
 *
 * This fake firewall actions resource implements the same interface as the real firewall actions resource for testing purposes.
 * It allows you to mock API responses and assert that specific firewall action requests
 * were made during testing.
 */
final class FirewallActionsFake implements ResourceContract
{
    /**
     * Create a new fake firewall actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions for a firewall (fake implementation).
     *
     * @param  string  $firewallId  The ID of the firewall
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $firewallId, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'firewall_actions',
            'method' => 'list',
            'parameters' => array_merge(['firewallId' => $firewallId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($firewallId, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['firewallId' => $firewallId], $parameters));
    }

    /**
     * Get a specific action for a firewall (fake implementation).
     *
     * @param  string  $firewallId  The ID of the firewall
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $firewallId, string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'firewall_actions',
            'method' => 'get',
            'parameters' => ['firewallId' => $firewallId, 'actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionRequest($firewallId, $actionId));
        }

        return ActionResponse::fake(['firewallId' => $firewallId, 'actionId' => $actionId]);
    }

    /**
     * Apply a firewall to resources (fake implementation).
     *
     * @param  string  $firewallId  The ID of the firewall
     * @param  array<string, mixed>  $parameters  The resources to apply the firewall to
     *
     * @throws Throwable When a mock exception is provided
     */
    public function applyToResources(string $firewallId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'firewall_actions',
            'method' => 'apply_to_resources',
            'parameters' => array_merge(['firewallId' => $firewallId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ApplyToResourcesRequest($firewallId, $parameters));
        }

        return ActionResponse::fake(array_merge(['firewallId' => $firewallId, 'command' => 'apply_to_resources'], $parameters));
    }

    /**
     * Remove a firewall from resources (fake implementation).
     *
     * @param  string  $firewallId  The ID of the firewall
     * @param  array<string, mixed>  $parameters  The resources to remove the firewall from
     *
     * @throws Throwable When a mock exception is provided
     */
    public function removeFromResources(string $firewallId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'firewall_actions',
            'method' => 'remove_from_resources',
            'parameters' => array_merge(['firewallId' => $firewallId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new RemoveFromResourcesRequest($firewallId, $parameters));
        }

        return ActionResponse::fake(array_merge(['firewallId' => $firewallId, 'command' => 'remove_from_resources'], $parameters));
    }

    /**
     * Set rules for a firewall (fake implementation).
     *
     * @param  string  $firewallId  The ID of the firewall
     * @param  array<string, mixed>  $parameters  The firewall rules to set
     *
     * @throws Throwable When a mock exception is provided
     */
    public function setRules(string $firewallId, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'firewall_actions',
            'method' => 'set_rules',
            'parameters' => array_merge(['firewallId' => $firewallId], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new SetRulesRequest($firewallId, $parameters));
        }

        return ActionResponse::fake(array_merge(['firewallId' => $firewallId, 'command' => 'set_rules'], $parameters));
    }

    /**
     * Assert that a request was sent to the firewall actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'firewall_actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to firewall_actions.');
        }
    }

    /**
     * Assert that no requests were sent to the firewall actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'firewall_actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to firewall_actions.');
        }
    }
}
