<?php

namespace Boci\HetznerLaravel\Requests\Networks;

use Boci\HetznerLaravel\Requests\Request;
use GuzzleHttp\Psr7\Query;

/**
 * List Network Members Request
 *
 * This request class is used to list the servers and load balancers
 * attached to a network in the Hetzner Cloud API.
 */
final class MembersRequest extends Request
{
    /**
     * Create a new list network members request instance.
     *
     * @param  string  $networkId  The ID of the network
     * @param  array<string, mixed>  $parameters  Optional query parameters (type, subnet, status, sort, page, per_page)
     */
    public function __construct(
        private readonly string $networkId,
        array $parameters = [],
    ) {
        parent::__construct($parameters);
    }

    /**
     * Get the HTTP method for this request.
     */
    public function method(): string
    {
        return 'GET';
    }

    /**
     * Get the URI for this request.
     */
    public function uri(): string
    {
        return "/v1/networks/{$this->networkId}/members";
    }

    /**
     * Get the request options for the HTTP client.
     *
     * Filters like type, subnet and status can be given more than once,
     * so arrays are sent as repeated keys (type=server&type=load_balancer).
     *
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return [
            'query' => Query::build($this->parameters),
        ];
    }
}
