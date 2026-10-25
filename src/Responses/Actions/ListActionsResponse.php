<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\Actions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Actions Response
 *
 * This response class represents the response from listing
 * actions from the Hetzner Cloud API.
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
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $actions = [];
        $actionCount = $parameters['count'] ?? 3;

        for ($i = 1; $i <= $actionCount; $i++) {
            $actions[] = [
                'id' => $i,
                'command' => 'create_server',
                'status' => 'success',
                'progress' => 100,
                'started' => '2023-01-01T12:00:00+00:00',
                'finished' => '2023-01-01T12:01:00+00:00',
                'resources' => [
                    [
                        'id' => $i,
                        'type' => 'server',
                    ],
                ],
                'error' => null,
            ];
        }

        $page = $parameters['page'] ?? 1;
        $perPage = $parameters['per_page'] ?? 25;
        $total = $parameters['total'] ?? count($actions);

        $data = [
            'actions' => $actions,
            'meta' => [
                'pagination' => [
                    'page' => $page,
                    'per_page' => $perPage,
                    'previous_page' => $page > 1 ? $page - 1 : null,
                    'next_page' => $page * $perPage < $total ? $page + 1 : null,
                    'last_page' => ceil($total / $perPage),
                    'total_entries' => $total,
                ],
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
