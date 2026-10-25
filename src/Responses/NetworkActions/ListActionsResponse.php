<?php

namespace Boci\HetznerLaravel\Responses\NetworkActions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Network Actions Response
 *
 * This response class represents the response from listing
 * network actions in the Hetzner Cloud API.
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
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters for the fake response
     */
    public static function fake(array $parameters = []): self
    {
        $data = [
            'actions' => [
                [
                    'id' => 1,
                    'command' => 'add_route',
                    'status' => 'running',
                    'progress' => 0,
                    'started' => '2016-01-30T23:50:00+00:00',
                    'finished' => null,
                    'resources' => [
                        [
                            'id' => 1,
                            'type' => 'network',
                        ],
                    ],
                    'error' => null,
                ],
                [
                    'id' => 2,
                    'command' => 'add_subnet',
                    'status' => 'success',
                    'progress' => 100,
                    'started' => '2016-01-30T23:45:00+00:00',
                    'finished' => '2016-01-30T23:50:00+00:00',
                    'resources' => [
                        [
                            'id' => 1,
                            'type' => 'network',
                        ],
                    ],
                    'error' => null,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
