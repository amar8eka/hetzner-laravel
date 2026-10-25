<?php

namespace Boci\HetznerLaravel\Responses\PlacementGroups;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Placement Groups Response
 *
 * This response class represents the response from listing
 * placement groups in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the placement groups from the response.
     *
     * @return PlacementGroup[]
     */
    public function placementGroups(): array
    {
        return array_map(
            fn (array $placementGroup) => new PlacementGroup($placementGroup),
            $this->data['placement_groups'] ?? []
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
        $data = [
            'placement_groups' => [
                [
                    'id' => 1,
                    'name' => 'my-placement-group',
                    'labels' => [
                        'environment' => 'production',
                        'team' => 'backend',
                    ],
                    'type' => 'spread',
                    'created' => '2016-01-30T23:50:00+00:00',
                    'servers' => [4711, 4712],
                ],
                [
                    'id' => 2,
                    'name' => 'my-placement-group-2',
                    'labels' => [
                        'environment' => 'development',
                        'team' => 'frontend',
                    ],
                    'type' => 'anti_affinity',
                    'created' => '2016-01-30T23:55:00+00:00',
                    'servers' => [4713],
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
