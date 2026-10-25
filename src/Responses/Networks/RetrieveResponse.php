<?php

namespace Boci\HetznerLaravel\Responses\Networks;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Retrieve Network Response
 *
 * This response class represents the response from retrieving
 * a network in the Hetzner Cloud API.
 */
final class RetrieveResponse extends Response
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
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $networkId = $parameters['networkId'] ?? '1';

        $data = [
            'network' => [
                'id' => (int) $networkId,
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
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
