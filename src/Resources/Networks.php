<?php

namespace Boci\HetznerLaravel\Resources;

use Boci\HetznerLaravel\Contracts\ResourceContract;
use Boci\HetznerLaravel\Requests\Networks\CreateRequest;
use Boci\HetznerLaravel\Requests\Networks\DeleteRequest;
use Boci\HetznerLaravel\Requests\Networks\ListRequest;
use Boci\HetznerLaravel\Requests\Networks\MembersRequest;
use Boci\HetznerLaravel\Requests\Networks\RetrieveRequest;
use Boci\HetznerLaravel\Requests\Networks\UpdateRequest;
use Boci\HetznerLaravel\Responses\Networks\CreateResponse;
use Boci\HetznerLaravel\Responses\Networks\DeleteResponse;
use Boci\HetznerLaravel\Responses\Networks\ListResponse;
use Boci\HetznerLaravel\Responses\Networks\MembersResponse;
use Boci\HetznerLaravel\Responses\Networks\RetrieveResponse;
use Boci\HetznerLaravel\Responses\Networks\UpdateResponse;
use GuzzleHttp\ClientInterface;

/**
 * Networks Resource
 *
 * This resource class provides methods for managing networks
 * in the Hetzner Cloud API.
 */
final class Networks implements ResourceContract
{
    /**
     * Create a new networks resource instance.
     *
     * @param  ClientInterface  $httpClient  The HTTP client instance
     */
    public function __construct(
        private readonly ClientInterface $httpClient,
    ) {}

    /**
     * List all networks
     *
     * @param  array<string, mixed>  $parameters  Optional query parameters for filtering
     */
    public function list(array $parameters = []): ListResponse
    {
        $request = new ListRequest($parameters);

        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return ListResponse::from($response, $request);
    }

    /**
     * Create a new network
     *
     * @param  array<string, mixed>  $parameters  The network creation parameters
     */
    public function create(array $parameters): CreateResponse
    {
        $request = new CreateRequest($parameters);

        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return CreateResponse::from($response, $request);
    }

    /**
     * Get a specific network by ID
     *
     * @param  string  $networkId  The ID of the network to retrieve
     */
    public function retrieve(string $networkId): RetrieveResponse
    {
        $request = new RetrieveRequest($networkId);

        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return RetrieveResponse::from($response, $request);
    }

    /**
     * Update a network
     *
     * @param  string  $networkId  The ID of the network to update
     * @param  array<string, mixed>  $parameters  The update parameters
     */
    public function update(string $networkId, array $parameters): UpdateResponse
    {
        $request = new UpdateRequest($networkId, $parameters);

        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return UpdateResponse::from($response, $request);
    }

    /**
     * Delete a network
     *
     * @param  string  $networkId  The ID of the network to delete
     */
    public function delete(string $networkId): DeleteResponse
    {
        $request = new DeleteRequest($networkId);

        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return DeleteResponse::from($response, $request);
    }

    /**
     * List the servers and load balancers attached to a network.
     *
     * @param  string  $networkId  The ID of the network
     * @param  array<string, mixed>  $parameters  Optional query parameters (type, subnet, status, sort, page, per_page)
     */
    public function members(string $networkId, array $parameters = []): MembersResponse
    {
        $request = new MembersRequest($networkId, $parameters);

        $response = $this->httpClient->request($request->method(), $request->uri(), $request->options());

        return MembersResponse::from($response, $request);
    }

    /**
     * Get access to network actions
     */
    public function actions(): NetworkActions
    {
        return new NetworkActions($this->httpClient);
    }
}
