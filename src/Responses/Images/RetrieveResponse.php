<?php

namespace Boci\HetznerLaravel\Responses\Images;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * Retrieve Image Response
 *
 * This response class represents the response from retrieving
 * an image in the Hetzner Cloud API.
 */
final class RetrieveResponse extends Response
{
    /**
     * Get the image from the response.
     */
    public function image(): Image
    {
        return new Image($this->data['image']);
    }

    /**
     * Create a fake response for testing.
     *
     * @param  array<string, mixed>  $parameters  Optional parameters
     */
    public static function fake(array $parameters = []): self
    {
        $imageId = $parameters['imageId'] ?? '1';

        $data = [
            'image' => [
                'id' => (int) $imageId,
                'type' => 'snapshot',
                'status' => 'available',
                'name' => 'test-image-'.$imageId,
                'description' => 'Test image description',
                'image_size' => 2.3,
                'disk_size' => 10,
                'created' => '2023-01-01T00:00:00+00:00',
                'created_from' => [
                    'id' => 1,
                    'name' => 'test-server',
                ],
                'bound_to' => null,
                'os_flavor' => 'ubuntu',
                'os_version' => '20.04',
                'rapid_deploy' => false,
                'protection' => [
                    'delete' => false,
                ],
                'deprecated' => null,
                'labels' => [],
            ],
        ];
        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
