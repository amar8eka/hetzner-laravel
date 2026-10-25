<?php

namespace Boci\HetznerLaravel\Responses\Images;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;
use Boci\HetznerLaravel\Responses\ServerActions\Action;

/**
 * Delete Image Response
 *
 * This response class represents the response from deleting
 * an image in the Hetzner Cloud API.
 */
final class DeleteResponse extends Response
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
        $data = [
            'action' => [
                'id' => 1,
                'command' => 'delete_image',
                'status' => 'success',
                'progress' => 100,
                'started' => '2023-01-01T00:00:00+00:00',
                'finished' => '2023-01-01T00:00:01+00:00',
                'resources' => [
                    [
                        'id' => (int) ($parameters['imageId'] ?? 1),
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
