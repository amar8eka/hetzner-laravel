<?php

namespace Boci\HetznerLaravel\Responses\FloatingIPs;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

final class CreateResponse extends Response
{
    public function floatingIp(): FloatingIP
    {
        return new FloatingIP($this->data['floating_ip']);
    }

    /**
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
            'floating_ip' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'test-floating-ip',
                'description' => $parameters['description'] ?? 'Test Floating IP',
                'ip' => '1.2.3.4',
                'type' => $parameters['type'] ?? 'ipv4',
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
            'action' => [
                'id' => 1,
                'command' => 'create_floating_ip',
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'floating_ip',
                    ],
                ],
                'error' => null,
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
