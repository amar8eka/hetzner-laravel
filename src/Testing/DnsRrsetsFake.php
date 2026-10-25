<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Responses\DnsRrsets\ActionResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\CreateResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\DeleteResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\GetResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\ListResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\UpdateResponse;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * DNS RRSets Fake
 *
 * This fake DNS RRSets resource implements ResourceContract for testing purposes.
 * It allows you to mock API responses and assert that specific DNS RRSet requests
 * were made during testing.
 */
final class DnsRrsetsFake implements ResourceContract
{
    /**
     * Create a new fake DNS RRSets resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all RRSets for a DNS zone (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(string $zoneIdOrName, array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'list',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ListResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\ListRequest($zoneIdOrName, $parameters));
        }

        return ListResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters));
    }

    /**
     * Create a new RRSet (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  array<string, mixed>  $parameters  The RRSet creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(string $zoneIdOrName, array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'create',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return CreateResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\CreateRequest($zoneIdOrName, $parameters));
        }

        return CreateResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters));
    }

    /**
     * Get a specific RRSet (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $zoneIdOrName, string $rrName, string $rrType): GetResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'get',
            'parameters' => ['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return GetResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\GetRequest($zoneIdOrName, $rrName, $rrType));
        }

        return GetResponse::fake(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType]);
    }

    /**
     * Update an RRSet (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'update',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\UpdateRequest($zoneIdOrName, $rrName, $rrType, $parameters));
        }

        return UpdateResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType], $parameters));
    }

    /**
     * Delete an RRSet (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $zoneIdOrName, string $rrName, string $rrType): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'delete',
            'parameters' => ['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\DeleteRequest($zoneIdOrName, $rrName, $rrType));
        }

        return DeleteResponse::fake(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType]);
    }

    /**
     * Change an RRSet's protection (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The protection parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeProtection(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'change_protection',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\ChangeProtectionRequest($zoneIdOrName, $rrName, $rrType, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'change_protection'], $parameters));
    }

    /**
     * Change an RRSet's TTL (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The TTL parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function changeTtl(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'change_ttl',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\ChangeTtlRequest($zoneIdOrName, $rrName, $rrType, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'change_ttl'], $parameters));
    }

    /**
     * Set records of an RRSet (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The records parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function setRecords(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'set_records',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\SetRecordsRequest($zoneIdOrName, $rrName, $rrType, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'set_rrset_records'], $parameters));
    }

    /**
     * Add records to an RRSet (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The records parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function addRecords(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'add_records',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\AddRecordsRequest($zoneIdOrName, $rrName, $rrType, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'add_rrset_records'], $parameters));
    }

    /**
     * Update the records of an RRSet, e.g. their comments (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The records parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function updateRecords(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'update_records',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\UpdateRecordsRequest($zoneIdOrName, $rrName, $rrType, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'update_rrset_records'], $parameters));
    }

    /**
     * Remove records from an RRSet (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The records parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function removeRecords(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $this->requests[] = [
            'resource' => 'dns_rrsets',
            'method' => 'remove_records',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName, 'rrName' => $rrName, 'rrType' => $rrType], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ActionResponse::from($response, new \Boci\HetznerLaravel\Requests\DnsRrsets\RemoveRecordsRequest($zoneIdOrName, $rrName, $rrType, $parameters));
        }

        return ActionResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName, 'command' => 'remove_rrset_records'], $parameters));
    }

    /**
     * Assert that a request was sent to the DNS RRSets resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'dns_rrsets');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new \PHPUnit\Framework\AssertionFailedError('No requests were sent to dns_rrsets.');
        }
    }

    /**
     * Assert that no requests were sent to the DNS RRSets resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'dns_rrsets');

        if (! empty($sent)) {
            throw new \PHPUnit\Framework\AssertionFailedError('Requests were sent to dns_rrsets.');
        }
    }
}
