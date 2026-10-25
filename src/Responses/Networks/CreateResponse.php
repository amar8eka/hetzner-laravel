<?php

namespace Boci\HetznerLaravel\Responses\Networks;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Create Network Response
 *
 * This response class represents the response from creating
 * a network in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the network from the response.
     *
     * @return array<string, mixed>
     */
    public function network(): array
    {
        return $this->data['network'] ?? [];
    }

    /**
     * Get the action from the response.
     *
     * @return array<string, mixed>
     */
    public function action(): array
    {
        return $this->data['action'] ?? [];
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'network' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'mynet',
                'ip_range' => $parameters['ip_range'] ?? '10.0.0.0/16',
                'subnets' => $parameters['subnets'] ?? [
                    [
                        'type' => 'cloud',
                        'ip_range' => '10.0.1.0/24',
                        'network_zone' => 'eu-central',
                        'gateway' => '10.0.1.1',
                    ],
                ],
                'routes' => $parameters['routes'] ?? [
                    [
                        'destination' => '10.100.1.0/24',
                        'gateway' => '10.0.1.1',
                    ],
                ],
                'servers' => [],
                'protection' => [
                    'delete' => false,
                ],
                'labels' => $parameters['labels'] ?? [
                    'environment' => 'production',
                ],
                'created' => '2016-01-30T23:50:00+00:00',
            ],
            'action' => [
                'id' => 1,
                'command' => 'create_network',
                'status' => 'running',
                'progress' => 0,
                'started' => '2016-01-30T23:50:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'network',
                    ],
                ],
                'error' => null,
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
