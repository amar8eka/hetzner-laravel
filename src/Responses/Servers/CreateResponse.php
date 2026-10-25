<?php

namespace Boci\HetznerLaravel\Responses\Servers;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Create Server Response
 *
 * This response class represents the response from creating a server
 * in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the created server.
     */
    public function server(): Server
    {
        return new Server($this->data['server']);
    }

    /**
     * Get the creation action.
     */
    public function action(): Action
    {
        return new Action($this->data['action']);
    }

    /**
     * Get the root password for the server.
     */
    public function rootPassword(): ?string
    {
        return $this->data['root_password'] ?? null;
    }

    /**
     * Create a fake response for testing purposes.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for customization
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'server' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'test-server',
                'status' => 'initializing',
                'created' => '2023-01-01T00:00:00+00:00',
                'public_net' => [
                    'ipv4' => [
                        'ip' => '1.2.3.4',
                        'blocked' => false,
                        'dns_ptr' => 'server.example.com',
                    ],
                    'ipv6' => [
                        'ip' => '2001:db8::1',
                        'blocked' => false,
                        'dns_ptr' => [],
                    ],
                ],
                'private_net' => [],
                'server_type' => [
                    'id' => 1,
                    'name' => $parameters['server_type'] ?? 'cpx11',
                    'description' => 'CPX11',
                    'cores' => 2,
                    'memory' => 4.0,
                    'disk' => 40,
                ],
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
                ],
                'image' => [
                    'id' => 1,
                    'type' => 'system',
                    'status' => 'available',
                    'name' => $parameters['image'] ?? 'ubuntu-24.04',
                    'description' => 'Ubuntu 24.04',
                    'image_size' => 2.3,
                    'disk_size' => 10.0,
                    'created' => '2023-01-01T00:00:00+00:00',
                    'os_flavor' => 'ubuntu',
                    'os_version' => '24.04',
                    'rapid_deploy' => true,
                ],
                'iso' => null,
                'rescue_enabled' => false,
                'locked' => false,
                'backup_window' => null,
                'outgoing_traffic' => null,
                'ingoing_traffic' => null,
                'included_traffic' => 21990232555520,
                'protection' => [
                    'delete' => false,
                    'rebuild' => false,
                ],
                'labels' => [],
                'volumes' => [],
                'load_balancers' => [],
            ],
            'action' => [
                'id' => 1,
                'command' => 'create_server',
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'server',
                    ],
                ],
                'error' => null,
            ],
            'root_password' => 'test-password',
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
