<?php

namespace Boci\HetznerLaravel\Testing;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\DnsZones\CreateRequest;
use Boci\HetznerLaravel\Requests\DnsZones\DeleteRequest;
use Boci\HetznerLaravel\Requests\DnsZones\ExportRequest;
use Boci\HetznerLaravel\Requests\DnsZones\ImportRequest;
use Boci\HetznerLaravel\Requests\DnsZones\ListRequest;
use Boci\HetznerLaravel\Requests\DnsZones\RetrieveRequest;
use Boci\HetznerLaravel\Requests\DnsZones\UpdateRequest;
use Boci\HetznerLaravel\Responses\DnsZones\CreateResponse;
use Boci\HetznerLaravel\Responses\DnsZones\DeleteResponse;
use Boci\HetznerLaravel\Responses\DnsZones\ExportResponse;
use Boci\HetznerLaravel\Responses\DnsZones\ImportResponse;
use Boci\HetznerLaravel\Responses\DnsZones\ListResponse;
use Boci\HetznerLaravel\Responses\DnsZones\RetrieveResponse;
use Boci\HetznerLaravel\Responses\DnsZones\UpdateResponse;
use PHPUnit\Framework\AssertionFailedError;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * DNS Zones Fake
 *
 * This fake DNS zones resource implements ResourceContract for testing purposes.
 * It allows you to mock API responses and assert that specific DNS zone requests
 * were made during testing.
 */
final class DnsZonesFake implements ResourceContract
{
    /**
     * Create a new fake DNS zones resource instance.
     *
     * @param  array<int, ResponseInterface|Throwable>  $responses  The mock responses
     * @param  array<int, array{resource: string, method: string, parameters: array}>  $requests  Reference to requests array
     */
    public function __construct(
        private array &$responses,
        private array &$requests,
    ) {}

    /**
     * List all DNS zones (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function list(array $parameters = []): ListResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zones',
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
     * Create a new DNS zone (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The DNS zone creation parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function create(array $parameters): CreateResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zones',
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
     * Get a specific DNS zone (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone to retrieve
     *
     * @throws Throwable When a mock exception is provided
     */
    public function retrieve(string $zoneIdOrName): RetrieveResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zones',
            'method' => 'get',
            'parameters' => ['zoneIdOrName' => $zoneIdOrName],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return RetrieveResponse::from($response, new RetrieveRequest($zoneIdOrName));
        }

        return RetrieveResponse::fake(['zoneIdOrName' => $zoneIdOrName]);
    }

    /**
     * Update a DNS zone (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone to update
     * @param  array<string, mixed>  $parameters  The update parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function update(string $zoneIdOrName, array $parameters): UpdateResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zones',
            'method' => 'update',
            'parameters' => array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters),
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return UpdateResponse::from($response, new UpdateRequest($zoneIdOrName, $parameters));
        }

        return UpdateResponse::fake(array_merge(['zoneIdOrName' => $zoneIdOrName], $parameters));
    }

    /**
     * Delete a DNS zone (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone to delete
     *
     * @throws Throwable When a mock exception is provided
     */
    public function delete(string $zoneIdOrName): DeleteResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zones',
            'method' => 'delete',
            'parameters' => ['zoneIdOrName' => $zoneIdOrName],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return DeleteResponse::from($response, new DeleteRequest($zoneIdOrName));
        }

        return DeleteResponse::fake(['zoneIdOrName' => $zoneIdOrName]);
    }

    /**
     * Export a DNS zone file (fake implementation).
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone to export
     *
     * @throws Throwable When a mock exception is provided
     */
    public function export(string $zoneIdOrName): ExportResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zones',
            'method' => 'export',
            'parameters' => ['zoneIdOrName' => $zoneIdOrName],
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ExportResponse::from($response, new ExportRequest($zoneIdOrName));
        }

        return ExportResponse::fake(['zoneIdOrName' => $zoneIdOrName]);
    }

    /**
     * Import a DNS zone file (fake implementation).
     *
     * @param  array<string, mixed>  $parameters  The import parameters
     *
     * @throws Throwable When a mock exception is provided
     */
    public function import(array $parameters): ImportResponse
    {
        $this->requests[] = [
            'resource' => 'dns_zones',
            'method' => 'import',
            'parameters' => $parameters,
        ];

        $response = array_shift($this->responses);

        if ($response instanceof Throwable) {
            throw $response;
        }

        if ($response instanceof ResponseInterface) {
            return ImportResponse::from($response, new ImportRequest($parameters));
        }

        return ImportResponse::fake($parameters);
    }

    /**
     * Get access to DNS zone actions (fake implementation).
     */
    public function actions(): DnsZoneActionsFake
    {
        return new DnsZoneActionsFake($this->responses, $this->requests);
    }

    /**
     * Get access to DNS RRSets (fake implementation).
     */
    public function rrsets(): DnsRrsetsFake
    {
        return new DnsRrsetsFake($this->responses, $this->requests);
    }

    /**
     * Assert that a request was sent to the DNS zones resource.
     *
     * @param  callable  $callback  The callback to filter requests
     */
    public function assertSent(callable $callback): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'dns_zones');

        $sent = array_filter($sent, $callback);

        if (empty($sent)) {
            throw new AssertionFailedError('No requests were sent to dns_zones.');
        }
    }

    /**
     * Assert that no requests were sent to the DNS zones resource.
     */
    public function assertNotSent(): void
    {
        $sent = array_filter($this->requests, fn (array $request) => $request['resource'] === 'dns_zones');

        if (! empty($sent)) {
            throw new AssertionFailedError('Requests were sent to dns_zones.');
        }
    }
}
