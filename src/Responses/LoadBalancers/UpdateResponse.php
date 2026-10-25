<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancers;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Update Load Balancer Response
 *
 * This response class represents the response from updating
 * a load balancer in the Hetzner Cloud API.
 */
final class UpdateResponse extends Response
{
    /**
     * Get the load balancer from the response.
     */
    public function loadBalancer(): LoadBalancer
    {
        return new LoadBalancer($this->data['load_balancer']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $loadBalancerId = $parameters['loadBalancerId'] ?? '1';

        $data = [
            'load_balancer' => [
                'id' => (int) $loadBalancerId,
                'name' => $parameters['name'] ?? 'updated-load-balancer',
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
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
