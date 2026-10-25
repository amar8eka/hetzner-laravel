<?php

namespace Boci\HetznerLaravel\Responses\ImageActions;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Image Action Response
 *
 * This response class represents the response from an image action
 * in the Hetzner Cloud API.
 */
final class ActionResponse extends Response
{
    /**
     * Get the action from the response.
     */
    public function action(): Action
    {
        return new Action($this->data['action']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $imageId = $parameters['imageId'] ?? '1';
        $actionId = $parameters['actionId'] ?? '1';
        $command = $parameters['command'] ?? 'change_protection';

        $data = [
            'action' => [
                'id' => (int) $actionId,
                'command' => $command,
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
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
