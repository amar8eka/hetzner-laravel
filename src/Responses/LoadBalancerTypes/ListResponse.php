<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancerTypes;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Load Balancer Types Response
 *
 * This response class represents the response from listing
 * load balancer types in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the load balancer types from the response.
     *
     * @return LoadBalancerType[]
     */
    public function loadBalancerTypes(): array
    {
        return array_map(
            fn (array $loadBalancerType) => new LoadBalancerType($loadBalancerType),
            $this->data['load_balancer_types'] ?? []
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
            'load_balancer_types' => [
                [
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
                        [
                            'location' => 'fsn1',
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
                [
                    'id' => 2,
                    'name' => 'lb21',
                    'description' => 'Load Balancer 21',
                    'max_connections' => 2000,
                    'max_services' => 20,
                    'max_targets' => 20,
                    'max_assigned_certificates' => 20,
                    'prices' => [
                        [
                            'location' => 'nbg1',
                            'price_hourly' => [
                                'net' => '2.0000000000',
                                'gross' => '2.3800000000',
                            ],
                            'price_monthly' => [
                                'net' => '2.0000000000',
                                'gross' => '2.3800000000',
                            ],
                        ],
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
                    'total_entries' => 2,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
