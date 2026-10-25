<?php

namespace Boci\HetznerLaravel\Responses\Networks;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Networks Response
 *
 * This response class represents the response from listing
 * networks in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the networks from the response.
     *
     * @return array<string, mixed>
     */
    public function networks(): array
    {
        return $this->data['networks'] ?? [];
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'networks' => [
                [
                    'id' => 1,
                    'name' => 'mynet',
                    'ip_range' => '10.0.0.0/16',
                    'subnets' => [
                        [
                            'type' => 'cloud',
                            'ip_range' => '10.0.1.0/24',
                            'network_zone' => 'eu-central',
                            'gateway' => '10.0.1.1',
                        ],
                    ],
                    'routes' => [
                        [
                            'destination' => '10.100.1.0/24',
                            'gateway' => '10.0.1.1',
                        ],
                    ],
                    'servers' => [4711],
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [
                        'environment' => 'production',
                    ],
                    'created' => '2016-01-30T23:50:00+00:00',
                ],
                [
                    'id' => 2,
                    'name' => 'mynet2',
                    'ip_range' => '192.168.0.0/16',
                    'subnets' => [
                        [
                            'type' => 'cloud',
                            'ip_range' => '192.168.1.0/24',
                            'network_zone' => 'eu-central',
                            'gateway' => '192.168.1.1',
                        ],
                    ],
                    'routes' => [],
                    'servers' => [],
                    'protection' => [
                        'delete' => true,
                    ],
                    'labels' => [
                        'environment' => 'development',
                    ],
                    'created' => '2016-01-30T23:55:00+00:00',
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
