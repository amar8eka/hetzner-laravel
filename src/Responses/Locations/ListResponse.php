<?php

namespace Boci\HetznerLaravel\Responses\Locations;

use Boci\HetznerLaravel\Meta\MetaInformation;
use Boci\HetznerLaravel\Responses\Response;

/**
 * List Locations Response
 *
 * This response class represents the response from listing
 * locations in the Hetzner Cloud API.
 */
final class ListResponse extends Response
{
    /**
     * Get the locations from the response.
     *
     * @return Location[]
     */
    public function locations(): array
    {
        return array_map(
            fn (array $location): Location => new Location($location),
            $this->data['locations'] ?? []
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
            'locations' => [
                [
                    'id' => 1,
                    'name' => 'nbg1',
                    'description' => 'Nuremberg DC 1',
                    'country' => 'DE',
                    'city' => 'Nuremberg',
                    'latitude' => 49.4521,
                    'longitude' => 11.0767,
                    'network_zone' => 'eu-central',
                ],
                [
                    'id' => 2,
                    'name' => 'fsn1',
                    'description' => 'Falkenstein DC 1',
                    'country' => 'DE',
                    'city' => 'Falkenstein',
                    'latitude' => 50.4761,
                    'longitude' => 12.3703,
                    'network_zone' => 'eu-central',
                ],
                [
                    'id' => 3,
                    'name' => 'hel1',
                    'description' => 'Helsinki DC 1',
                    'country' => 'FI',
                    'city' => 'Helsinki',
                    'latitude' => 60.1699,
                    'longitude' => 24.9384,
                    'network_zone' => 'eu-central',
                ],
                [
                    'id' => 4,
                    'name' => 'ash',
                    'description' => 'Ashburn',
                    'country' => 'US',
                    'city' => 'Ashburn',
                    'latitude' => 39.0438,
                    'longitude' => -77.4874,
                    'network_zone' => 'us-east',
                ],
                [
                    'id' => 5,
                    'name' => 'hil',
                    'description' => 'Hillsboro',
                    'country' => 'US',
                    'city' => 'Hillsboro',
                    'latitude' => 45.5192,
                    'longitude' => -122.9892,
                    'network_zone' => 'us-west',
                ],
            ],
            'meta' => [
                'pagination' => [
                    'page' => 1,
                    'per_page' => 25,
                    'previous_page' => null,
                    'next_page' => null,
                    'last_page' => 1,
                    'total_entries' => 5,
                ],
            ],
        ];

        $meta = new MetaInformation;

        return new self($data, $meta);
    }
}
