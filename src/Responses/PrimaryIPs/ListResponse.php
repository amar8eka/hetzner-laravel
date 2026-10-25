<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\PrimaryIPs;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Primary IPs Response
 *
 * This response class represents the response from listing
 * primary IPs in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the primary IPs from the response.
     *
     * @return PrimaryIP[]
     */
    public function primaryIps(): array
    {
        return array_map(
            fn (array $primaryIp) => new PrimaryIP($primaryIp),
            $this->data['primary_ips'] ?? []
        );
    }

    /**
     * Get the pagination information from the response.
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
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'primary_ips' => [
                [
                    'id' => 1,
                    'name' => 'test-primary-ip-1',
                    'ip' => '1.2.3.4',
                    'type' => 'ipv4',
                    'assignee_id' => null,
                    'assignee_type' => null,
                    'auto_delete' => false,
                    'blocked' => false,
                    'created' => '2023-01-01T00:00:00+00:00',
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
                        'server_types' => [
                            'supported' => [1, 2, 3],
                            'available' => [1, 2, 3],
                            'available_for_migration' => [1, 2, 3],
                        ],
                    ],
                    'dns_ptr' => [
                        [
                            'ip' => '1.2.3.4',
                            'dns_ptr' => 'primary-ip.example.com',
                        ],
                    ],
                    'home_location' => [
                        'id' => 1,
                        'name' => 'nbg1',
                        'description' => 'Nuremberg',
                        'country' => 'DE',
                        'city' => 'Nuremberg',
                        'latitude' => 49.4521,
                        'longitude' => 11.0767,
                        'network_zone' => 'eu-central',
                    ],
                    'labels' => [],
                    'protection' => [
                        'delete' => false,
                    ],
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
