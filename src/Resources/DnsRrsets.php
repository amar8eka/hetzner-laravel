<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Resources;

use Boci\HetznerLaravel\Requests\DnsRrsets\AddRecordsRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\ChangeProtectionRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\ChangeTtlRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\CreateRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\DeleteRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\GetRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\ListRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\RemoveRecordsRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\SetRecordsRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\UpdateRecordsRequest;
use Boci\HetznerLaravel\Requests\DnsRrsets\UpdateRequest;
use Boci\HetznerLaravel\Responses\DnsRrsets\ActionResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\CreateResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\DeleteResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\GetResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\ListResponse;
use Boci\HetznerLaravel\Responses\DnsRrsets\UpdateResponse;
use GuzzleHttp\ClientInterface;

/**
 * DNS RRSets Resource
 *
 * This resource class provides methods for managing DNS RRSets
 * in the Hetzner Cloud API.
 */
final class DnsRrsets
{
    /**
     * Create a new DNS RRSets resource instance.
     *
     * @param  ClientInterface  $httpClient  The HTTP client instance
     */
    public function __construct(
        private readonly ClientInterface $httpClient
    ) {}

    /**
     * List all RRSets for a DNS zone
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  array<string, mixed>  $parameters  Optional query parameters for filtering
     */
    public function list(string $zoneIdOrName, array $parameters = []): ListResponse
    {
        $request = new ListRequest($zoneIdOrName, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return ListResponse::from($response, $request);
    }

    /**
     * Create a new RRSet
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  array<string, mixed>  $parameters  The RRSet creation parameters
     */
    public function create(string $zoneIdOrName, array $parameters): CreateResponse
    {
        $request = new CreateRequest($zoneIdOrName, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return CreateResponse::from($response, $request);
    }

    /**
     * Get a specific RRSet
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     */
    public function retrieve(string $zoneIdOrName, string $rrName, string $rrType): GetResponse
    {
        $request = new GetRequest($zoneIdOrName, $rrName, $rrType);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return GetResponse::from($response, $request);
    }

    /**
     * Update an RRSet
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The update parameters
     */
    public function update(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): UpdateResponse
    {
        $request = new UpdateRequest($zoneIdOrName, $rrName, $rrType, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return UpdateResponse::from($response, $request);
    }

    /**
     * Delete an RRSet
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     */
    public function delete(string $zoneIdOrName, string $rrName, string $rrType): DeleteResponse
    {
        $request = new DeleteRequest($zoneIdOrName, $rrName, $rrType);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return DeleteResponse::from($response, $request);
    }

    /**
     * Change an RRSet's protection
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The protection parameters
     */
    public function changeProtection(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $request = new ChangeProtectionRequest($zoneIdOrName, $rrName, $rrType, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return ActionResponse::from($response, $request);
    }

    /**
     * Change an RRSet's TTL
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The TTL parameters
     */
    public function changeTtl(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $request = new ChangeTtlRequest($zoneIdOrName, $rrName, $rrType, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return ActionResponse::from($response, $request);
    }

    /**
     * Set records of an RRSet
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The records parameters
     */
    public function setRecords(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $request = new SetRecordsRequest($zoneIdOrName, $rrName, $rrType, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return ActionResponse::from($response, $request);
    }

    /**
     * Add records to an RRSet
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The records parameters
     */
    public function addRecords(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $request = new AddRecordsRequest($zoneIdOrName, $rrName, $rrType, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return ActionResponse::from($response, $request);
    }

    /**
     * Update the records of an RRSet, e.g. their comments
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The records to update
     */
    public function updateRecords(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $request = new UpdateRecordsRequest($zoneIdOrName, $rrName, $rrType, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return ActionResponse::from($response, $request);
    }

    /**
     * Remove records from an RRSet
     *
     * @param  string  $zoneIdOrName  The ID or name of the DNS zone
     * @param  string  $rrName  The name of the RRSet
     * @param  string  $rrType  The type of the RRSet
     * @param  array<string, mixed>  $parameters  The records parameters
     */
    public function removeRecords(string $zoneIdOrName, string $rrName, string $rrType, array $parameters): ActionResponse
    {
        $request = new RemoveRecordsRequest($zoneIdOrName, $rrName, $rrType, $parameters);
        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return ActionResponse::from($response, $request);
    }
}
