<?php

namespace Boci\HetznerLaravel\Responses\FloatingIPs;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

final class UpdateResponse extends Response
{
    public function floatingIp(): FloatingIP
    {
        return new FloatingIP($this->data['floating_ip']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $floatingIpId = $parameters['floatingIpId'] ?? '1';

        $data = [
            'floating_ip' => [
                'id' => (int) $floatingIpId,
                'name' => $parameters['name'] ?? 'updated-floating-ip',
                'description' => $parameters['description'] ?? 'Updated Floating IP',
                'ip' => '1.2.3.4',
                'type' => 'ipv4',
                'server' => null,
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
                'labels' => $parameters['labels'] ?? [],
                'created' => '2023-01-01T00:00:00+00:00',
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
