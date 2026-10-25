<?php

namespace Boci\HetznerLaravel\Responses\PlacementGroups;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;
use Boci\HetznerLaravel\Responses\ServerActions\Action;

/**
 * Create Placement Group Response
 *
 * This response class represents the response from creating
 * a placement group in the Hetzner Cloud API.
 */
final class CreateResponse extends Response
{
    /**
     * Get the placement group from the response.
     */
    public function placementGroup(): PlacementGroup
    {
        return new PlacementGroup($this->data['placement_group']);
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
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'placement_group' => [
                'id' => 1,
                'name' => $parameters['name'] ?? 'my-placement-group',
                'labels' => $parameters['labels'] ?? [
                    'environment' => 'production',
                    'team' => 'backend',
                ],
                'type' => $parameters['type'] ?? 'spread',
                'created' => '2016-01-30T23:50:00+00:00',
                'servers' => [],
            ],
            'action' => [
                'id' => 1,
                'command' => 'create_placement_group',
                'status' => 'running',
                'progress' => 0,
                'started' => '2016-01-30T23:50:00+00:00',
                'finished' => null,
                'resources' => [
                    [
                        'id' => 1,
                        'type' => 'placement_group',
                    ],
                ],
                'error' => null,
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
