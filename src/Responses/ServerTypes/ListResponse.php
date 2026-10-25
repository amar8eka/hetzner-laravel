<?php

namespace Boci\HetznerLaravel\Responses\ServerTypes;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Server Types Response
 *
 * This response class represents the response from listing
 * server types in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the server types from the response.
     *
     * @return ServerType[]
     */
    public function serverTypes(): array
    {
        return array_map(
            fn (array $serverType): ServerType => new ServerType($serverType),
            $this->data['server_types'] ?? []
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
            'server_types' => [
                [
                    'id' => 1,
                    'name' => 'cx11',
                    'description' => 'CX11',
                    'cores' => 1,
                    'memory' => 4.0,
                    'disk' => 20,
                    'prices' => [
                        [
                            'location' => 'fsn1',
                            'price_hourly' => [
                                'net' => '1.0000000000',
                                'gross' => '1.1900000000000000',
                            ],
                            'price_monthly' => [
                                'net' => '1.0000000000',
                                'gross' => '1.1900000000000000',
                            ],
                        ],
                    ],
                    'storage_type' => 'local',
                    'cpu_type' => 'shared',
                ],
                [
                    'id' => 2,
                    'name' => 'cx21',
                    'description' => 'CX21',
                    'cores' => 2,
                    'memory' => 8.0,
                    'disk' => 40,
                    'prices' => [
                        [
                            'location' => 'fsn1',
                            'price_hourly' => [
                                'net' => '2.0000000000',
                                'gross' => '2.3800000000000000',
                            ],
                            'price_monthly' => [
                                'net' => '2.0000000000',
                                'gross' => '2.3800000000000000',
                            ],
                        ],
                    ],
                    'storage_type' => 'local',
                    'cpu_type' => 'shared',
                ],
                [
                    'id' => 3,
                    'name' => 'cx31',
                    'description' => 'CX31',
                    'cores' => 2,
                    'memory' => 8.0,
                    'disk' => 80,
                    'prices' => [
                        [
                            'location' => 'fsn1',
                            'price_hourly' => [
                                'net' => '3.0000000000',
                                'gross' => '3.5700000000000000',
                            ],
                            'price_monthly' => [
                                'net' => '3.0000000000',
                                'gross' => '3.5700000000000000',
                            ],
                        ],
                    ],
                    'storage_type' => 'local',
                    'cpu_type' => 'shared',
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 3,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
