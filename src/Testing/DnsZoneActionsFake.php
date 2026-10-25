<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\DnsZoneActions\ChangeDefaultTtlRequest;
use Boci\HetznerLaravel\Requests\DnsZoneActions\ChangeNameserversRequest;
use Boci\HetznerLaravel\Requests\DnsZoneActions\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\DnsZoneActions\GetActionRequest;
use Boci\HetznerLaravel\Requests\DnsZoneActions\ListActionsRequest;
use Boci\HetznerLaravel\Responses\DnsZoneActions\ActionResponse;
use Boci\HetznerLaravel\Responses\DnsZoneActions\ListActionsResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * DNS Zone Actions Fake
 *
 * This fake DNS zone actions resource implements ResourceContract for testing purposes.
 * It allows you to mock API responses and assert that specific DNS zone action requests
 * were made during testing.
 */
final class DnsZoneActionsFake implements ResourceContract
{
    /**
     * Create a new fake DNS zone actions resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all actions for a DNS zone (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $zoneIdOrName, array $parameters = []): ListActionsResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zone_actions',
            'method' => 'list',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListActionsResponse::from($response, new ListActionsRequest($zoneIdOrName, $parameters));
        }

        return ListActionsResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters));
    }

    /**
     * Get a specific action for a DNS zone (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $actionId  The ID of the action to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $zoneIdOrName, string $actionId): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zone_actions',
            'method' => 'get',
            'parameters' => ['zoneIdOrName' => $zoneIdOrName, 'actionId' => $actionId],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new GetActionRequest($zoneIdOrName, $actionId));
        }

        return ActionResponse::fake(['zoneIdOrName' => $zoneIdOrName, 'actionId' => $actionId]);
    }

    /**
     * Change a zone's primary nameservers (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  array<string, mixed>  $parameters  The nameserver parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeNameservers(string $zoneIdOrName, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zone_actions',
            'method' => 'change_nameservers',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeNameserversRequest($zoneIdOrName, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'change_nameservers'], $parameters));
    }

    /**
     * Change a zone's protection (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $zoneIdOrName, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zone_actions',
            'method' => 'change_protection',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeProtectionRequest($zoneIdOrName, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'change_protection'], $parameters));
    }

    /**
     * Change a zone's default TTL (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  array<string, mixed>  $parameters  The TTL parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeDefaultTtl(string $zoneIdOrName, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zone_actions',
            'method' => 'change_default_ttl',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new ChangeDefaultTtlRequest($zoneIdOrName, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'change_default_ttl'], $parameters));
    }

    /**
     * Assert that a request was sent to the DNS zone actions resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'dns_zone_actions');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to dns_zone_actions.');
        }
    }

    /**
     * Assert that no requests were sent to the DNS zone actions resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'dns_zone_actions');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to dns_zone_actions.');
        }
    }
}
