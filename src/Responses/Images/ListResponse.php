<?php

namespace Boci\HetznerLaravel\Responses\Images;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Images Response
 *
 * This response class represents the response from listing
 * images in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the images from the response.
     *
     * @return Image[]
     */
    public function images(): array
    {
        return array_map(
            fn (array $image): Image => new Image($image),
            $this->data['images'] ?? []
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
            'images' => [
                [
                    'id' => 1,
                    'type' => 'snapshot',
                    'status' => 'available',
                    'name' => 'test-image',
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
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 1,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
