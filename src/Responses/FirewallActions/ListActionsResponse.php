<?php

namespace Boci\HetznerLaravel\Responses\FirewallActions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Firewall Actions Response
 *
 * This response class represents the response from listing
 * firewall actions from the Hetzner Cloud API.
 */
final class ListActionsResponse extends Response
{
    /**
     * Get the actions from the response.
     *
     * @return array<string, mixed>
     */
    public function actions(): array
    {
        return $this->data['actions'] ?? [];
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
        $firewallId = $parameters['firewallId'] ?? '1';
        $actionCount = $parameters['count'] ?? 2;

        $actions = [];
        for ($i = 1; $i <= $actionCount; $i++) {
            $actions[] = [
                'id' => $i,
                'command' => 'apply_to_resources',
                'status' => 'success',
                'progress' => 100,
                'started' => '2023-01-01T12:00:00+00:00',
                'finished' => '2023-01-01T12:00:01+00:00',
                'resources' => [
                    [
                        'id' => (int) $firewallId,
                        'type' => 'firewall',
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
