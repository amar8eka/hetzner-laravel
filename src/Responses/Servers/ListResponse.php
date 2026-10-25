<?php

namespace Boci\HetznerLaravel\Responses\Servers;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Servers Response
 *
 * This response class represents the response from listing servers
 * in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the list of servers.
     *
     * @return Server[]
     */
    public function servers(): array
    {
        return array_map(
            fn (array $server): Server => new Server($server),
            $this->data['servers'] ?? []
        );
    }

    /**
     * Get the pagination information.
     *
     * @return array<string, mixed>
     */
    public function pagination(): array
    {
        $meta = $this->data['meta'] ?? [];
        $pagination = $meta['pagination'] ?? [];

        return [
            'current_page' => $pagination['page'] ?? 1,
            'per_page' => $pagination['per_page'] ?? 25,
            'total' => $pagination['total_entries'] ?? 0,
            'last_page' => $pagination['last_page'] ?? 1,
            'from' => (($pagination['page'] ?? 1) - 1) * ($pagination['per_page'] ?? 25) + 1,
            'to' => min(($pagination['page'] ?? 1) * ($pagination['per_page'] ?? 25), $pagination['total_entries'] ?? 0),
            'has_more_pages' => ($pagination['next_page'] ?? null) !== null,
            'links' => [
                'first' => $pagination['page'] > 1 ? '?page=1' : null,
                'last' => $pagination['last_page'] > 1 ? '?page='.$pagination['last_page'] : null,
                'prev' => $pagination['previous_page'] ? '?page='.$pagination['previous_page'] : null,
                'next' => $pagination['next_page'] ? '?page='.$pagination['next_page'] : null,
            ],
        ];
    }

    /**
     * Create a fake response for testing purposes.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for customization
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'servers' => [
                [
                    'id' => 1,
                    'name' => 'test-server-1',
                    'status' => 'running',
                    'created' => '2023-01-01T00:00:00+00:00',
                    'public_net' => [
                        'ipv4' => [
                            'ip' => '1.2.3.4',
                            'blocked' => false,
                            'dns_ptr' => 'server1.example.com',
                        ],
                        'ipv6' => [
                            'ip' => '2001:db8::1',
                            'blocked' => false,
                            'dns_ptr' => [],
                        ],
                    ],
                    'private_net' => [],
                    'server_type' => [
                        'id' => 1,
                        'name' => 'cpx11',
                        'description' => 'CPX11',
                        'cores' => 2,
                        'memory' => 4.0,
                        'disk' => 40,
                    ],
                    'datacenter' => [
                        'id' => 1,
                        'name' => 'nbg1-dc3',
                        'description' => 'Nuremberg 1 DC 3',
                        'location' => [
                            'id' => 1,
                            'name' => 'nbg1',
                            'description' => 'Nuremberg',
                            'country' => 'DE',
                            'city' => 'Nuremberg',
                            'latitude' => 49.4521,
                            'longitude' => 11.0767,
                            'network_zone' => 'eu-central',
                        ],
                    ],
                    'image' => [
                        'id' => 1,
                        'type' => 'system',
                        'status' => 'available',
                        'name' => 'ubuntu-24.04',
                        'description' => 'Ubuntu 24.04',
                        'image_size' => 2.3,
                        'disk_size' => 10.0,
                        'created' => '2023-01-01T00:00:00+00:00',
                        'os_flavor' => 'ubuntu',
                        'os_version' => '24.04',
                        'rapid_deploy' => true,
                    ],
                    'iso' => null,
                    'rescue_enabled' => false,
                    'locked' => false,
                    'backup_window' => null,
                    'outgoing_traffic' => null,
                    'ingoing_traffic' => null,
                    'included_traffic' => 21990232555520,
                    'protection' => [
                        'delete' => false,
                        'rebuild' => false,
                    ],
                    'labels' => [],
                    'volumes' => [],
                    'load_balancers' => [],
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 1,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
