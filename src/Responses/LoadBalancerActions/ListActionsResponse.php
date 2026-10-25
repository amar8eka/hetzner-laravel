<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\LoadBalancerActions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Load Balancer Actions Response
 *
 * This response class represents the response from listing
 * load balancer actions in the Hetzner Cloud API.
 */
final class ListActionsResponse extends Response
{
    /**
     * Get the actions from the response.
     *
     * @return Action[]
     */
    public function actions(): array
    {
        return array_map(
            fn (array $action) => new Action($action),
            $this->data['actions'] ?? []
        );
    }

    /**
     * Get the pagination information from the response.
     *
     * @return array<string, mixed>
     */
    public function pagination(): array
    {
        $meta = $this->data['meta'] ?? [];
        $pagination = $meta['pagination'] ?? [];

        return [
            'current_page' => $pagination['page'] ?? 1,
            'per_page' => $pagination['per_page'] ?? 25,
            'total' => $pagination['total_entries'] ?? 0,
            'last_page' => $pagination['last_page'] ?? 1,
            'from' => (($pagination['page'] ?? 1) - 1) * ($pagination['per_page'] ?? 25) + 1,
            'to' => min(($pagination['page'] ?? 1) * ($pagination['per_page'] ?? 25), $pagination['total_entries'] ?? 0),
            'has_more_pages' => ($pagination['next_page'] ?? null) !== null,
            'links' => [
                'first' => $pagination['page'] > 1 ? '?page=1' : null,
                'last' => $pagination['last_page'] > 1 ? '?page='.$pagination['last_page'] : null,
                'prev' => $pagination['previous_page'] ? '?page='.$pagination['previous_page'] : null,
                'next' => $pagination['next_page'] ? '?page='.$pagination['next_page'] : null,
            ],
        ];
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $loadBalancerId = $parameters['loadBalancerId'] ?? '1';

        $data = [
            'actions' => [
                [
                    'id' => 1,
                    'command' => 'add_service',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2023-01-01T00:00:00+00:00',
                    'finished' => '2023-01-01T00:01:00+00:00',
                    'resources' => [
                        [
                            'id' => (int) $loadBalancerId,
                            'type' => 'load_balancer',
                        ],
                    ],
                    'error' => null,
                ],
                [
                    'id' => 2,
                    'command' => 'add_target',
                    'status' => 'running',
                    'progress' => 50,
                    'started' => '2023-01-01T00:02:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => (int) $loadBalancerId,
                            'type' => 'load_balancer',
                        ],
                    ],
                    'error' => null,
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 2,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
