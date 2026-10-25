<?php

declare(strict_types=1);

namespace Boci\HetznerLaravel\Responses\Volumes;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Volumes Response
 *
 * This response class represents the response from listing
 * volumes in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the volumes from the response.
     *
     * @return Volume[]
     */
    public function volumes(): array
    {
        return array_map(
            fn (array $volume) => new Volume($volume),
            $this->data['volumes'] ?? []
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
            'volumes' => [
                [
                    'id' => 1,
                    'name' => 'my-volume',
                    'status' => 'available',
                    'server' => null,
                    'location' => [
                        'id' => 1,
                        'name' => 'fsn1',
                        'description' => 'Falkenstein DC Park 1',
                        'country' => 'DE',
                        'city' => 'Falkenstein',
                        'latitude' => 50.4762,
                        'longitude' => 12.3707,
                        'network_zone' => 'eu-central',
                    ],
                    'size' => 10,
                    'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_1',
                    'protection' => [
                        'delete' => false,
                    ],
                    'labels' => [
                        'environment' => 'production',
                        'team' => 'backend',
                    ],
                    'created' => '2023-01-01T00:00:00+00:00',
                ],
                [
                    'id' => 2,
                    'name' => 'another-volume',
                    'status' => 'creating',
                    'server' => 42,
                    'location' => [
                        'id' => 2,
                        'name' => 'nbg1',
                        'description' => 'Nuremberg DC Park 1',
                        'country' => 'DE',
                        'city' => 'Nuremberg',
                        'latitude' => 49.4521,
                        'longitude' => 11.0767,
                        'network_zone' => 'eu-central',
                    ],
                    'size' => 20,
                    'linux_device' => '/dev/disk/by-id/scsi-0HC_Volume_2',
                    'protection' => [
                        'delete' => true,
                    ],
                    'labels' => [
                        'environment' => 'staging',
                        'team' => 'frontend',
                    ],
                    'created' => '2023-01-02T10:30:00+00:00',
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
