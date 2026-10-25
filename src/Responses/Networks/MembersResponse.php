<?php

namespace Boci\HetznerLaravel\Responses\Networks;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Network Members Response
 *
 * This response class represents the response from listing the servers
 * and load balancers attached to a network in the Hetzner Cloud API.
 */
final class MembersResponse extends Response
{
    /**
     * Get the members of the network.
     *
     * Each member has a type (server or load_balancer), id, ip, alias_ips,
     * subnet and status (ok, attaching, detaching, updating or error).
     *
     * @return array<int, array<string, mixed>>
     */
    public function members(): array
    {
        return $this->data['members'] ?? [];
    }

    /**
     * Get the pagination information from the response.
     *
     * @return array<string, mixed>
     */
    public function pagination(): array
    {
        return $this->data['meta']['pagination'] ?? [];
    }

    /**
     * Create a fake response for testing purposes.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for customization
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'members' => [
                [
                    'type' => 'server',
                    'id' => 42,
                    'ip' => '10.0.1.1',
                    'alias_ips' => [],
                    'subnet' => '10.0.1.0/24',
                    'status' => 'ok',
                ],
                [
                    'type' => 'load_balancer',
                    'id' => 4711,
                    'ip' => '10.0.1.2',
                    'alias_ips' => [],
                    'subnet' => '10.0.1.0/24',
                    'status' => 'ok',
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 2,
                ],
            ],
        ];

        return new self($data, new MetaInformation);
    }
}
