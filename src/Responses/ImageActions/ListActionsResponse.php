<?php

namespace Boci\HetznerLaravel\Responses\ImageActions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Image Actions Response
 *
 * This response class represents the response from listing
 * image actions in the Hetzner Cloud API.
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
            $this->data['actions']
        );
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $imageId = $parameters['imageId'] ?? '1';
        $actionCount = $parameters['count'] ?? 2;

        $actions = [];
        for ($i = 1; $i <= $actionCount; $i++) {
            $actions[] = [
                'id' => $i,
                'command' => 'change_protection',
                'status' => 'success',
                'progress' => 100,
                'started' => '2023-01-01T12:00:00+00:00',
                'finished' => '2023-01-01T12:00:01+00:00',
                'resources' => [
                    [
                        'id' => (int) $imageId,
                        'type' => 'image',
                    ],
                ],
                'error' => null,
            ];
        }

        $data = [
            'actions' => $actions,
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
