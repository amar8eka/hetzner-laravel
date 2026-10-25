<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancerTypes;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Retrieve Load Balancer Type Response
 *
 * This response class represents the response from retrieving
 * a load balancer type in the Hetzner Cloud API.
 */
final class RetrieveResponse extends Response
{
    /**
     * Get the load balancer type from the response.
     */
    public function loadBalancerType(): LoadBalancerType
    {
        return new LoadBalancerType($this->data['load_balancer_type']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $loadBalancerTypeId = $parameters['loadBalancerTypeId'] ?? '1';

        $data = [
            'load_balancer_type' => [
                'id' => (int) $loadBalancerTypeId,
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
                    [
                        'location' => 'fsn1',
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
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
