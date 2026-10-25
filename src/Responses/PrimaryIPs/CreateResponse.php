<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\PrimaryIPs;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;
use Boci\HetznerLaravel\Responses\ServerActions\Action;

/**
 * Create Primary IP Response
 *
 * This response class represents the response from creating
 * a primary IP in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the primary IP from the response.
     */
    public function primaryIp(): PrimaryIP
    {
        return new PrimaryIP($this->data['primary_ip']);
    }

    /**
     * Get the action from the response.
     */
    public function action(): ?Action
    {
        if (! isset($this->data['action'])) {
            return null;
        }

        return new Action($this->data['action']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'primary_ip' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'test-primary-ip',
                'ip' => '1.2.3.4',
                'type' => $parameters['type'] ?? 'ipv4',
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
                'labels' => $parameters['labels'] ?? [],
                'protection' => [
                    'delete' => false,
                ],
            ],
            'action' => [
                'id' => 1,
                'command' => 'create_primary_ip',
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'primary_ip',
                    ],
                ],
                'error' => null,
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
