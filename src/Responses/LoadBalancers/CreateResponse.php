<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancers;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;
use Boci\HetznerLaravel\Responses\ServerActions\Action;

/**
 * Create Load Balancer Response
 *
 * This response class represents the response from creating
 * a load balancer in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the load balancer from the response.
     */
    public function loadBalancer(): LoadBalancer
    {
        return new LoadBalancer($this->data['load_balancer']);
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
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'load_balancer' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'test-load-balancer',
                'public_net' => [
                    'ipv4' => [
                        'ip' => '1.2.3.4',
                        'dns_ptr' => 'lb.example.com',
                    ],
                    'ipv6' => [
                        'ip' => '2001:db8::1',
                        'dns_ptr' => 'lb.example.com',
                    ],
                ],
                'private_net' => [],
                'location' => [
                    'id' => 1,
                    'name' => 'nbg1',
                    'description' => 'Nuremberg 1',
                    'country' => 'DE',
                    'city' => 'Nuremberg',
                    'latitude' => 49.4521,
                    'longitude' => 11.0767,
                    'network_zone' => 'eu-central',
                ],
                'load_balancer_type' => [
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
                    ],
                ],
                'algorithm' => [
                    'type' => 'round_robin',
                ],
                'included_traffic' => 1000,
                'ingoing_traffic' => 100,
                'outgoing_traffic' => 200,
                'services' => [],
                'targets' => [],
                'protection' => [
                    'delete' => false,
                ],
                'labels' => [],
                'created' => '2023-01-01T00:00:00+00:00',
            ],
            'action' => [
                'id' => 1,
                'command' => 'create_load_balancer',
                'status' => 'running',
                'progress' => 0,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'load_balancer',
                    ],
                ],
                'error' => null,
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
