<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancers;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Load Balancer Metrics Response
 *
 * This response class represents the response from retrieving
 * load balancer metrics in the Hetzner Cloud API.
 */
final class MetricsResponse extends Response
{
    /**
     * Get the metrics from the response.
     */
    public function metrics(): LoadBalancerMetrics
    {
        return new LoadBalancerMetrics($this->data['metrics']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'metrics' => [
                'start' => '2023-01-01T00:00:00+00:00',
                'end' => '2023-01-01T01:00:00+00:00',
                'step' => 60,
                'time_series' => [
                    'connections_per_second' => [
                        'values' => [
                            ['1.0', '2.0', '3.0'],
                        ],
                        'times' => [
                            '2023-01-01T00:00:00+00:00',
                            '2023-01-01T00:01:00+00:00',
                            '2023-01-01T00:02:00+00:00',
                        ],
                    ],
                    'requests_per_second' => [
                        'values' => [
                            ['10.0', '20.0', '30.0'],
                        ],
                        'times' => [
                            '2023-01-01T00:00:00+00:00',
                            '2023-01-01T00:01:00+00:00',
                            '2023-01-01T00:02:00+00:00',
                        ],
                    ],
                ],
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
