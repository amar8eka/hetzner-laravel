<?php

namespace Boci\HetznerLaravel\Responses\FloatingIPs;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

final class ListResponse extends Response
{
    /**
     * @return FloatingIP[]
     */
    public function floatingIps(): array
    {
        return array_map(
            fn (array $floatingIp): FloatingIP => new FloatingIP($floatingIp),
            $this->data['floating_ips'] ?? []
        );
    }

    /**
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
     * @param  array<string, mixed>  $parameters
     */
    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'floating_ips' => [
                [
                    'id' => 1,
                    'name' => 'test-floating-ip-1',
                    'description' => 'Test Floating IP',
                    'ip' => '1.2.3.4',
                    'type' => 'ipv4',
                    'server' => 1,
                    'dns_ptr' => [
                        [
                            'ip' => '1.2.3.4',
                            'dns_ptr' => 'floating-ip.example.com',
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
                    'blocked' => false,
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
