<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancers;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Load Balancers Response
 *
 * This response class represents the response from listing
 * load balancers in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the load balancers from the response.
     *
     * @return LoadBalancer[]
     */
    public function loadBalancers(): array
    {
        return array_map(
            fn (array $loadBalancer) => new LoadBalancer($loadBalancer),
            $this->data['load_balancers'] ?? []
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
            'load_balancers' => [
                [
                    'id' => 1,
                    'name' => 'test-load-balancer',
                    'public_net' => [
                        'ipv4' => [
                            'ip' => '1.2.3.4',
                            'dns_ptr' => 'lb.example.com',
                        ],
                        'ipv6' => [
                            'ip' => '2001:db8::1',
                            'dns_ptr' => 'lb.example.com',
                        ],
                    ],
                    'private_net' => [],
                    'location' => [
                        'id' => 1,
                        'name' => 'nbg1',
                        'description' => 'Nuremberg 1',
                        'country' => 'DE',
                        'city' => 'Nuremberg',
                        'latitude' => 49.4521,
                        'longitude' => 11.0767,
                        'network_zone' => 'eu-central',
                    ],
                    'load_balancer_type' => [
                        'id' => 1,
                        'name' => 'lb11',
                        'description' => 'Load Balancer 11',
                        'max_connections' => 1000,
                        'max_services' => 10,
                        'max_targets' => 10,
                        'max_assigned_certificates' => 10,
                        'prices' => [
                            [
                                'location' => 'nbg1',
                                'price_hourly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000',
                                ],
                                'price_monthly' => [
                                    'net' => '1.0000000000',
                                    'gross' => '1.1900000000',
                                ],
                            ],
                        ],
                    ],
                    'algorithm' => [
                        'type' => 'round_robin',
                    ],
                    'included_traffic' => 1000,
                    'ingoing_traffic' => 100,
                    'outgoing_traffic' => 200,
                    'services' => [],
                    'targets' => [],
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [],
                    'created' => '2023-01-01T00:00:00+00:00',
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
