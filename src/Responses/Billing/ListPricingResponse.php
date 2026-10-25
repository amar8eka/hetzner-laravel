<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\Billing;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Pricing Response
 *
 * This response class represents the response from listing
 * pricing information from the Hetzner Cloud API.
 */
final class ListPricingResponse extends Response
{
    /**
     * Get the pricing information from the response.
     */
    public function pricing(): Pricing
    {
        return new Pricing($this->data['pricing']);
    }

    /**
     * Create a fake response for testing purposes.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for customization
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'pricing' => [
                'currency' => 'EUR',
                'vat_rate' => '19.00',
                'image' => [
                    'price_per_gb_month' => [
                        'net' => '0.0040000000',
                        'gross' => '0.0047600000',
                    ],
                ],
                'floating_ip' => [
                    'price_monthly' => [
                        'net' => '1.1900000000',
                        'gross' => '1.4161000000',
                    ],
                ],
                'floating_ip_type' => [
                    'ipv4' => [
                        'price_monthly' => [
                            'net' => '1.1900000000',
                            'gross' => '1.4161000000',
                        ],
                    ],
                    'ipv6' => [
                        'price_monthly' => [
                            'net' => '1.1900000000',
                            'gross' => '1.4161000000',
                        ],
                    ],
                ],
                'load_balancer_type' => [
                    'lb11' => [
                        'price_monthly' => [
                            'net' => '3.2900000000',
                            'gross' => '3.9151000000',
                        ],
                    ],
                    'lb21' => [
                        'price_monthly' => [
                            'net' => '5.8300000000',
                            'gross' => '6.9377000000',
                        ],
                    ],
                    'lb31' => [
                        'price_monthly' => [
                            'net' => '10.8300000000',
                            'gross' => '12.8877000000',
                        ],
                    ],
                ],
                'primary_ip' => [
                    'price_monthly' => [
                        'net' => '1.1900000000',
                        'gross' => '1.4161000000',
                    ],
                ],
                'primary_ip_type' => [
                    'ipv4' => [
                        'price_monthly' => [
                            'net' => '1.1900000000',
                            'gross' => '1.4161000000',
                        ],
                    ],
                    'ipv6' => [
                        'price_monthly' => [
                            'net' => '1.1900000000',
                            'gross' => '1.4161000000',
                        ],
                    ],
                ],
                'server_type' => [
                    'cx11' => [
                        'price_monthly' => [
                            'net' => '2.9600000000',
                            'gross' => '3.5224000000',
                        ],
                    ],
                    'cx21' => [
                        'price_monthly' => [
                            'net' => '5.8300000000',
                            'gross' => '6.9377000000',
                        ],
                    ],
                    'cx31' => [
                        'price_monthly' => [
                            'net' => '11.6600000000',
                            'gross' => '13.8754000000',
                        ],
                    ],
                    'cx41' => [
                        'price_monthly' => [
                            'net' => '22.3200000000',
                            'gross' => '26.5608000000',
                        ],
                    ],
                    'cx51' => [
                        'price_monthly' => [
                            'net' => '44.6400000000',
                            'gross' => '53.1216000000',
                        ],
                    ],
                ],
                'traffic' => [
                    'price_per_tb' => [
                        'net' => '1.0000000000',
                        'gross' => '1.1900000000',
                    ],
                ],
                'volume' => [
                    'price_per_gb_month' => [
                        'net' => '0.0400000000',
                        'gross' => '0.0476000000',
                    ],
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
