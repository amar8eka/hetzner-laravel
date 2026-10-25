<?php

namespace Boci\HetznerLaravel\Responses\Servers;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Server Metrics Response
 *
 * This response class represents the response from retrieving
 * server metrics in the Hetzner Cloud API.
 */
final class MetricsResponse extends Response
{
    /**
     * Get the metrics from the response.
     */
    public function metrics(): ServerMetrics
    {
        return new ServerMetrics($this->data['metrics']);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    public static function fake(array $parameters = []): self
    {
        $startTime = $parameters['start'] ?? now()->subHour()->toISOString();
        $endTime = $parameters['end'] ?? now()->toISOString();

        $data = [
            'metrics' => [
                'start' => $startTime,
                'end' => $endTime,
                'step' => 60,
                'time_series' => [
                    'cpu' => [
                        'values' => [
                            [0.1, 0.2, 0.15, 0.3, 0.25, 0.2, 0.18, 0.22],
                            [0.2, 0.3, 0.25, 0.4, 0.35, 0.3, 0.28, 0.32],
                        ],
                    ],
                    'disk' => [
                        '0.0' => [
                            'values' => [
                                [0.1, 0.2, 0.15, 0.3, 0.25, 0.2, 0.18, 0.22],
                                [0.2, 0.3, 0.25, 0.4, 0.35, 0.3, 0.28, 0.32],
                            ],
                        ],
                    ],
                    'network' => [
                        '0' => [
                            'values' => [
                                [1000, 2000, 1500, 3000, 2500, 2200, 1800, 2100],
                                [2000, 3000, 2500, 4000, 3500, 3200, 2800, 3100],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
